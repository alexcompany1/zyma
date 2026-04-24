<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

function hasTable(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name"
    );
    $stmt->execute([':table_name' => $table]);
    return (int)$stmt->fetchColumn() > 0;
}

function hasTableColumn(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name"
    );
    $stmt->execute([
        ':table_name' => $table,
        ':column_name' => $column
    ]);
    return (int)$stmt->fetchColumn() > 0;
}

function getCurrentUserRole(): string
{
    $workerCode = $_SESSION['worker_code'] ?? '';
    if ($workerCode === 'ADMIN') {
        return 'admin';
    }
    if ($workerCode !== '') {
        return 'trabajador';
    }
    return 'cliente';
}

function isAdmin(): bool
{
    return getCurrentUserRole() === 'admin';
}

function isWorker(): bool
{
    return getCurrentUserRole() === 'trabajador';
}

function isAdminOrWorker(): bool
{
    return isAdmin() || isWorker();
}

function requireRoles(array $roles): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        die("<h2 class='page-error'>Acceso denegado. Debes iniciar sesión.</h2>");
    }

    $role = getCurrentUserRole();
    if (!in_array($role, $roles, true)) {
        die("<h2 class='page-error'>Acceso denegado. No tienes permisos para ver esta página.</h2>");
    }
}

function ensureNotificationsSchema(PDO $pdo): void
{
    if (!hasTable($pdo, 'notificaciones')) {
        return;
    }

    if (!hasTableColumn($pdo, 'notificaciones', 'titulo')) {
        $pdo->exec("ALTER TABLE notificaciones ADD COLUMN titulo VARCHAR(255) NULL AFTER id_usuario");
    }
    if (!hasTableColumn($pdo, 'notificaciones', 'tipo')) {
        $pdo->exec("ALTER TABLE notificaciones ADD COLUMN tipo VARCHAR(50) NOT NULL DEFAULT 'info' AFTER mensaje");
    }
    if (!hasTableColumn($pdo, 'notificaciones', 'enlace')) {
        $pdo->exec("ALTER TABLE notificaciones ADD COLUMN enlace VARCHAR(255) NULL AFTER tipo");
    }
    if (!hasTableColumn($pdo, 'notificaciones', 'leida')) {
        $pdo->exec("ALTER TABLE notificaciones ADD COLUMN leida TINYINT(1) NOT NULL DEFAULT 0 AFTER enlace");
    }
    if (!hasTableColumn($pdo, 'notificaciones', 'fecha')) {
        $pdo->exec("ALTER TABLE notificaciones ADD COLUMN fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER leida");
    }
}

function createNotification(PDO $pdo, int $userId, string $title, string $message, ?string $link = null, string $type = 'info'): void
{
    if (!hasTable($pdo, 'notificaciones')) {
        return;
    }

    ensureNotificationsSchema($pdo);
    $stmt = $pdo->prepare(
        "INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo, enlace, leida, fecha) VALUES (:user_id, :title, :message, :type, :link, 0, NOW())"
    );
    $stmt->execute([
        ':user_id' => $userId,
        ':title' => $title,
        ':message' => $message,
        ':type' => $type,
        ':link' => $link,
    ]);
}

function getUnreadNotificationsCount(PDO $pdo, int $userId): int
{
    if (!hasTable($pdo, 'notificaciones')) {
        return 0;
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario = :user_id AND leida = 0");
    $stmt->execute([':user_id' => $userId]);
    return (int)$stmt->fetchColumn();
}

function getRecentNotifications(PDO $pdo, int $userId, int $limit = 10): array
{
    if (!hasTable($pdo, 'notificaciones')) {
        return [];
    }
    $stmt = $pdo->prepare("SELECT id, titulo, mensaje, tipo, enlace, leida, fecha FROM notificaciones WHERE id_usuario = :user_id ORDER BY fecha DESC LIMIT :limit");
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function markNotificationRead(PDO $pdo, int $notificationId, int $userId): void
{
    if (!hasTable($pdo, 'notificaciones')) {
        return;
    }
    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = 1 WHERE id = :id AND id_usuario = :user_id");
    $stmt->execute([':id' => $notificationId, ':user_id' => $userId]);
}

function markAllNotificationsRead(PDO $pdo, int $userId): void
{
    if (!hasTable($pdo, 'notificaciones')) {
        return;
    }
    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = 1 WHERE id_usuario = :user_id");
    $stmt->execute([':user_id' => $userId]);
}

function decrementStockForOrder(PDO $pdo, int $orderId): void
{
    if (!hasTable($pdo, 'pedido_items') || !hasTable($pdo, 'producto_ingredientes') || !hasTable($pdo, 'ingredientes')) {
        return;
    }

    $stmtItems = $pdo->prepare("SELECT id_producto, cantidad FROM pedido_items WHERE id_pedido = :order_id");
    $stmtItems->execute([':order_id' => $orderId]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $item) {
        $productId = (int)$item['id_producto'];
        $quantityOrdered = (float)$item['cantidad'];

        $stmtIngredients = $pdo->prepare(
            "SELECT ingrediente_id, cantidad_necesaria FROM producto_ingredientes WHERE producto_id = :product_id"
        );
        $stmtIngredients->execute([':product_id' => $productId]);
        $ingredients = $stmtIngredients->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ingredients as $ingredientRow) {
            $ingredientId = (int)$ingredientRow['ingrediente_id'];
            $unitsNeeded = (float)$ingredientRow['cantidad_necesaria'] * $quantityOrdered;

            $stmtUpdate = $pdo->prepare(
                "UPDATE ingredientes SET cantidad = cantidad - :delta WHERE id = :id AND cantidad >= :delta"
            );
            $stmtUpdate->execute([':delta' => $unitsNeeded, ':id' => $ingredientId]);

            if ($stmtUpdate->rowCount() !== 1) {
                throw new Exception('Stock insuficiente para el ingrediente ID ' . $ingredientId . '.');
            }

            $stmtCheck = $pdo->prepare("SELECT nombre, cantidad, stock_minimo, unidad FROM ingredientes WHERE id = :id");
            $stmtCheck->execute([':id' => $ingredientId]);
            $ingredientInfo = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            if ($ingredientInfo && isset($ingredientInfo['stock_minimo']) && (float)$ingredientInfo['cantidad'] < (float)$ingredientInfo['stock_minimo']) {
                createNotification(
                    $pdo,
                    $_SESSION['user_id'] ?? 0,
                    'Stock bajo de ' . $ingredientInfo['nombre'],
                    'El ingrediente ' . $ingredientInfo['nombre'] . ' está por debajo del mínimo (' . $ingredientInfo['cantidad'] . ' ' . ($ingredientInfo['unidad'] ?? '') . ').',
                    'admin_inventory.php',
                    'warning'
                );
            }
        }
    }
}

function changeOrderStatus(PDO $pdo, int $orderId, string $newStatus, int $actorId): void
{
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT estado, id_usuario FROM pedidos WHERE id_pedido = :id FOR UPDATE");
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            throw new Exception('Pedido no encontrado.');
        }

        $currentStatus = $order['estado'];
        $customerId = (int)$order['id_usuario'];

        if ($newStatus === $currentStatus) {
            $pdo->rollBack();
            return;
        }

        if ($newStatus === Order::STATUS_READY) {
            decrementStockForOrder($pdo, $orderId);
        }

        $stmt = $pdo->prepare("UPDATE pedidos SET estado = :status WHERE id_pedido = :id");
        $stmt->execute([':status' => $newStatus, ':id' => $orderId]);

        createNotification(
            $pdo,
            $customerId,
            'Pedido actualizado',
            'Tu pedido #' . $orderId . ' ha cambiado a ' . Order::statusLabel($newStatus) . '.',
            'mis_pedidos.php',
            'info'
        );

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function getRedIngredientsCount(PDO $pdo): int
{
    if (!hasTable($pdo, 'ingredientes') || !hasTableColumn($pdo, 'ingredientes', 'stock_minimo')) {
        return 0;
    }
    $stmt = $pdo->query("SELECT COUNT(*) FROM ingredientes WHERE cantidad < stock_minimo");
    return (int)$stmt->fetchColumn();
}

function getTopSellingProductToday(PDO $pdo): string
{
    if (!hasTable($pdo, 'pedido_items') || !hasTable($pdo, 'pedidos') || !hasTable($pdo, 'productos')) {
        return '-';
    }
    $stmt = $pdo->query(
        "SELECT pr.nombre FROM pedido_items pi
         JOIN pedidos p ON p.id_pedido = pi.id_pedido
         JOIN productos pr ON pr.id = pi.id_producto
         WHERE DATE(p.fecha_hora) = CURDATE()
         GROUP BY pr.id, pr.nombre
         ORDER BY SUM(pi.cantidad) DESC
         LIMIT 1"
    );
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    return $product['nombre'] ?? '-';
}

function getTodaySales(PDO $pdo): float
{
    if (!hasTable($pdo, 'pedidos')) {
        return 0.0;
    }
    $stmt = $pdo->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE DATE(fecha_hora) = CURDATE()");
    return (float)$stmt->fetchColumn();
}

function getTodayOrdersCount(PDO $pdo): int
{
    if (!hasTable($pdo, 'pedidos')) {
        return 0;
    }
    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE DATE(fecha_hora) = CURDATE()");
    return (int)$stmt->fetchColumn();
}

function getActiveOrdersCount(PDO $pdo): int
{
    if (!hasTable($pdo, 'pedidos')) {
        return 0;
    }
    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado IN ('pendiente', 'preparando', 'listo')");
    return (int)$stmt->fetchColumn();
}

function getDashboardStats(PDO $pdo): array
{
    return [
        'today_sales' => getTodaySales($pdo),
        'today_orders' => getTodayOrdersCount($pdo),
        'active_orders' => getActiveOrdersCount($pdo),
        'top_product_of_day' => getTopSellingProductToday($pdo),
        'red_ingredients' => getRedIngredientsCount($pdo)
    ];
}

function getHomeLink(): string
{
    return 'usuario.php';
}

function renderHomeButton(): string
{
    return '<a href="' . htmlspecialchars(getHomeLink()) . '" class="landing-link">Volver a inicio</a>';
}

function getUnreadNotificationBadge(PDO $pdo): int
{
    if (!isset($_SESSION['user_id'])) {
        return 0;
    }
    return getUnreadNotificationsCount($pdo, (int)$_SESSION['user_id']);
}

function getNotificationIconHtml(PDO $pdo): string
{
    $count = getUnreadNotificationBadge($pdo);
    return '<a href="admin_notifications.php" class="notification-link">🔔' .
           ($count > 0 ? '<span class="notification-count">' . $count . '</span>' : '') .
           '</a>';
}

function createAdminSchema(PDO $pdo): void
{
    if (hasTable($pdo, 'notificaciones')) {
        ensureNotificationsSchema($pdo);
    }

    if (hasTable($pdo, 'productos')) {
        if (!hasTableColumn($pdo, 'productos', 'available')) {
            $pdo->exec("ALTER TABLE productos ADD COLUMN available TINYINT(1) NOT NULL DEFAULT 1 AFTER precio");
        }
        if (!hasTableColumn($pdo, 'productos', 'category')) {
            $pdo->exec("ALTER TABLE productos ADD COLUMN category VARCHAR(100) NULL AFTER precio");
        }
        if (!hasTableColumn($pdo, 'productos', 'description')) {
            $pdo->exec("ALTER TABLE productos ADD COLUMN description TEXT NULL AFTER category");
        }
        if (!hasTableColumn($pdo, 'productos', 'deleted_at')) {
            $pdo->exec("ALTER TABLE productos ADD COLUMN deleted_at DATETIME NULL AFTER available");
        }
    }

    if (hasTable($pdo, 'ingredientes')) {
        if (!hasTableColumn($pdo, 'ingredientes', 'stock_minimo')) {
            $pdo->exec("ALTER TABLE ingredientes ADD COLUMN stock_minimo DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER cantidad");
        }
        if (!hasTableColumn($pdo, 'ingredientes', 'unidad')) {
            $pdo->exec("ALTER TABLE ingredientes ADD COLUMN unidad VARCHAR(50) NULL AFTER stock_minimo");
        }
    }

    if (hasTable($pdo, 'pedidos')) {
        if (!hasTableColumn($pdo, 'pedidos', 'estado')) {
            $pdo->exec("ALTER TABLE pedidos ADD COLUMN estado VARCHAR(50) NOT NULL DEFAULT 'pendiente' AFTER id_usuario");
        }
        if (!hasTableColumn($pdo, 'pedidos', 'notas_cliente')) {
            $pdo->exec("ALTER TABLE pedidos ADD COLUMN notas_cliente TEXT NULL AFTER total");
        }
        if (!hasTableColumn($pdo, 'pedidos', 'created_at') && hasTableColumn($pdo, 'pedidos', 'fecha_hora')) {
            $pdo->exec("ALTER TABLE pedidos ADD COLUMN created_at DATETIME NULL AFTER fecha_hora");
        }
    }

    if (hasTable($pdo, 'pedido_items') && !hasTableColumn($pdo, 'pedido_items', 'precio_unitario')) {
        $pdo->exec("ALTER TABLE pedido_items ADD COLUMN precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER cantidad");
    }
}

function getOrderCurrentStatus(PDO $pdo, int $orderId): ?string
{
    if (!hasTable($pdo, 'pedidos')) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT estado FROM pedidos WHERE id_pedido = :id LIMIT 1");
    $stmt->execute([':id' => $orderId]);
    $status = $stmt->fetchColumn();

    return $status !== false ? $status : null;
}

class Order
{
    public const STATUS_PENDING = 'pendiente';
    public const STATUS_PREPARING = 'preparando';
    public const STATUS_READY = 'listo';
    public const STATUS_DELIVERED = 'entregado';

    public static function allStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PREPARING,
            self::STATUS_READY,
            self::STATUS_DELIVERED,
        ];
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_PREPARING => 'En preparación',
            self::STATUS_READY => 'Listo',
            self::STATUS_DELIVERED => 'Entregado',
            default => 'Desconocido',
        };
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'stock-gris',
            self::STATUS_PREPARING => 'stock-amarillo',
            self::STATUS_READY => 'stock-verde',
            self::STATUS_DELIVERED => 'stock-gris',
            default => 'stock-gris',
        };
    }

    public static function nextStatus(string $currentStatus): string
    {
        return match ($currentStatus) {
            self::STATUS_PENDING => self::STATUS_PREPARING,
            self::STATUS_PREPARING => self::STATUS_READY,
            self::STATUS_READY => self::STATUS_DELIVERED,
            default => self::STATUS_DELIVERED,
        };
    }
}

class Ingredient
{
    public static function stockStatus(float $quantity, float $minimum): string
    {
        if ($quantity <= 0) {
            return 'rojo';
        }
        if ($quantity <= $minimum * 1.25) {
            return 'amarillo';
        }
        return 'verde';
    }

    public static function stockLabel(string $status): string
    {
        return match ($status) {
            'verde' => 'Suficiente',
            'amarillo' => 'Bajo',
            'rojo' => 'Crítico',
            default => 'Desconocido',
        };
    }
}

class Product
{
    public static function availableLabel(int $available): string
    {
        return $available === 1 ? 'Disponible' : 'No disponible';
    }
}

?>

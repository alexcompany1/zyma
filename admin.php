<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
?>
<?php
/**
 * admin.php
 * Panel de administracion de usuarios (solo ADMIN) con las rutas de navegacion bien hechas.
 */

session_start();
require_once 'config.php';
require_once 'auth.php';
zymaRequireRole('admin');

function hasTableColumn(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = :table_name
          AND COLUMN_NAME = :column_name
    ");
    $stmt->execute([
        ':table_name' => $table,
        ':column_name' => $column
    ]);

    return (int)$stmt->fetchColumn() > 0;
}

function roleFromWorkerCode(?string $workerCode): string
{
    if ($workerCode === 'ADMIN') {
        return 'admin';
    }
    if ($workerCode === null || $workerCode === '') {
        return 'cliente';
    }
    return 'trabajador';
}

function workerCodeFromRole(string $role, int $userId, ?string $customCode = null): ?string
{
    if ($role === 'admin') {
        return 'ADMIN';
    }
    if ($role === 'cliente') {
        return null;
    }
    if ($customCode !== null && trim($customCode) !== '') {
        return trim($customCode);
    }
    return 'TRAB' . str_pad((string)$userId, 3, '0', STR_PAD_LEFT);
}

function isValidWorkerCode(string $code): bool
{
    return (bool)preg_match('/^[A-Z0-9_-]{3,32}$/', $code);
}

function workerCodeExists(PDO $pdo, string $workerCode, ?int $excludeId = null): bool
{
    $sql = "SELECT COUNT(*) FROM usuarios WHERE worker_code = :worker_code";
    $params = [':worker_code' => $workerCode];
    if ($excludeId !== null) {
        $sql .= " AND id != :exclude_id";
        $params[':exclude_id'] = $excludeId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn() > 0;
}

function generateUniqueWorkerCode(PDO $pdo, int $userId): string
{
    $attempt = 0;
    do {
        $suffix = str_pad((string)($userId + $attempt), 3, '0', STR_PAD_LEFT);
        $code = 'TRAB' . $suffix;
        $attempt++;
    } while (workerCodeExists($pdo, $code) && $attempt < 10000);

    return $code;
}

function isSafeIdentifier(string $identifier): bool
{
    return (bool)preg_match('/^[A-Za-z0-9_]+$/', $identifier);
}

function deleteUserCascade(PDO $pdo, int $userId): bool
{
    $pdo->beginTransaction();

    try {
        // 1) Eliminar tablas que referencian pedidos, para luego poder borrar pedidos del usuario.
        $stmtRefsPedidos = $pdo->query("
            SELECT TABLE_NAME, COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
              AND REFERENCED_TABLE_NAME = 'pedidos'
              AND REFERENCED_COLUMN_NAME = 'id_pedido'
        ");
        $refsPedidos = $stmtRefsPedidos->fetchAll(PDO::FETCH_ASSOC);

        foreach ($refsPedidos as $ref) {
            $table = (string)$ref['TABLE_NAME'];
            $column = (string)$ref['COLUMN_NAME'];
            if (!isSafeIdentifier($table) || !isSafeIdentifier($column)) {
                continue;
            }
            $sql = "DELETE child FROM `$table` child INNER JOIN pedidos p ON child.`$column` = p.id_pedido WHERE p.id_usuario = :id_usuario";
            $stmtDeleteChild = $pdo->prepare($sql);
            $stmtDeleteChild->execute([':id_usuario' => $userId]);
        }

        // 2) Eliminar pedidos del usuario.
        $stmtDeletePedidos = $pdo->prepare("DELETE FROM pedidos WHERE id_usuario = :id_usuario");
        $stmtDeletePedidos->execute([':id_usuario' => $userId]);

        // 3) Eliminar tablas que referencian directamente usuarios (excepto pedidos, ya tratado).
        $stmtRefsUsuarios = $pdo->query("
            SELECT TABLE_NAME, COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
              AND REFERENCED_TABLE_NAME = 'usuarios'
              AND REFERENCED_COLUMN_NAME = 'id'
        ");
        $refsUsuarios = $stmtRefsUsuarios->fetchAll(PDO::FETCH_ASSOC);

        foreach ($refsUsuarios as $ref) {
            $table = (string)$ref['TABLE_NAME'];
            $column = (string)$ref['COLUMN_NAME'];
            if ($table === 'pedidos') {
                continue;
            }
            if (!isSafeIdentifier($table) || !isSafeIdentifier($column)) {
                continue;
            }
            $sql = "DELETE FROM `$table` WHERE `$column` = :id_usuario";
            $stmtDeleteDirect = $pdo->prepare($sql);
            $stmtDeleteDirect->execute([':id_usuario' => $userId]);
        }

        // 4) Eliminar usuario.
        $stmtDeleteUser = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmtDeleteUser->execute([':id' => $userId]);

        if ($stmtDeleteUser->rowCount() !== 1) {
            $pdo->rollBack();
            return false;
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

$show_cookie_popup = !empty($_SESSION['show_cookie_popup']);
$cookie_preferences = $_SESSION['cookie_preferences'] ?? [];
unset($_SESSION['show_cookie_popup']);

$supportsBloqueado = hasTableColumn($pdo, 'usuarios', 'bloqueado');
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'cliente';
    $workerCodeInput = trim($_POST['worker_code'] ?? '');

    if ($nombre === '' || $email === '' || $password === '') {
        $msg = "<div class='alert alert-error'>Nombre, email y Contraseña obligatorios.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "<div class='alert alert-error'>Formato de email inválido.</div>";
    } elseif (strlen($password) < 6) {
        $msg = "<div class='alert alert-error'>La Contraseña debe tener al menos 6 caracteres.</div>";
    } elseif (!in_array($role, ['cliente', 'trabajador', 'admin'], true)) {
        $msg = "<div class='alert alert-error'>Rol inválido.</div>";
    } elseif ($workerCodeInput !== '' && !isValidWorkerCode($workerCodeInput)) {
        $msg = "<div class='alert alert-error'>Código de trabajador inválido. Usa 3-32 caracteres A-Z, 0-9, _ o -.</div>";
    } elseif ($workerCodeInput !== '' && workerCodeExists($pdo, $workerCodeInput)) {
        $msg = "<div class='alert alert-error'>Ese código de trabajador ya está en uso.</div>";
    } else {
        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $workerCode = null;
            if ($role === 'admin') {
                $workerCode = 'ADMIN';
            }
            if ($role === 'trabajador' && $workerCodeInput !== '') {
                $workerCode = $workerCodeInput;
            }

            $sqlInsert = "INSERT INTO usuarios (nombre, email, password_hash, worker_code" . ($supportsBloqueado ? ", bloqueado" : "") . ") VALUES (:nombre, :email, :password_hash, :worker_code" . ($supportsBloqueado ? ", 0" : "") . ")";
            $stmt = $pdo->prepare($sqlInsert);
            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':password_hash' => $passwordHash,
                ':worker_code' => $workerCode
            ]);

            $newUserId = (int)$pdo->lastInsertId();
            if ($role === 'trabajador' && $workerCode === null) {
                $generatedCode = generateUniqueWorkerCode($pdo, $newUserId);
                $stmt = $pdo->prepare("UPDATE usuarios SET worker_code = :worker_code WHERE id = :id");
                $stmt->execute([
                    ':worker_code' => $generatedCode,
                    ':id' => $newUserId
                ]);
                $msg = "<div class='alert alert-success'>Usuario creado correctamente. Código de trabajador: " . htmlspecialchars($generatedCode) . ".</div>";
            } else {
                $msg = "<div class='alert alert-success'>Usuario creado correctamente.</div>";
            }
        } catch (Exception $e) {
            if ((string)$e->getCode() === '23000') {
                $msg = "<div class='alert alert-error'>El email ya existe.</div>";
            } else {
                $msg = "<div class='alert alert-error'>Error al crear usuario.</div>";
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = (string)($_POST['action'] ?? '');
    $targetId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    if (!in_array($action, ['delete', 'role', 'toggle_block'], true)) {
        $msg = "<div class='alert alert-error'>Acción inválida.</div>";
    } elseif (!$targetId) {
        $msg = "<div class='alert alert-error'>ID inválido.</div>";
    } elseif ($targetId === (int)$_SESSION['user_id']) {
        $msg = "<div class='alert alert-error'>No puedes aplicar esta accion sobre tu propio usuario.</div>";
    } else {
        try {
            $stmtUser = $pdo->prepare("SELECT id FROM usuarios WHERE id = :id");
            $stmtUser->execute([':id' => $targetId]);
            $targetExists = (bool)$stmtUser->fetchColumn();
            if (!$targetExists) {
                $msg = "<div class='alert alert-error'>Usuario no encontrado.</div>";
            } elseif ($action === 'delete') {
                try {
                    if (deleteUserCascade($pdo, (int)$targetId)) {
                        $msg = "<div class='alert alert-success'>Usuario eliminado correctamente.</div>";
                    } else {
                        $msg = "<div class='alert alert-error'>No se pudo eliminar el usuario.</div>";
                    }
                } catch (Exception $e) {
                    $msg = "<div class='alert alert-error'>No se pudo eliminar la cuenta. Detalle: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
            } elseif ($action === 'role') {
                $newRole = $_POST['new_role'] ?? '';
                if (!in_array($newRole, ['cliente', 'trabajador', 'admin'], true)) {
                    $msg = "<div class='alert alert-error'>Rol inválido.</div>";
                } else {
                    $customCode = trim($_POST['new_worker_code'] ?? '');
                    if ($newRole === 'trabajador' && $customCode !== '' && !isValidWorkerCode($customCode)) {
                        $msg = "<div class='alert alert-error'>Código de trabajador inválido. Usa 3-32 caracteres A-Z, 0-9, _ o -.</div>";
                    } elseif ($customCode !== '' && workerCodeExists($pdo, $customCode, (int)$targetId)) {
                        $msg = "<div class='alert alert-error'>Ese código de trabajador ya está en uso.</div>";
                    } else {
                        $newWorkerCode = workerCodeFromRole($newRole, (int)$targetId, $customCode !== '' ? $customCode : null);
                        if ($newRole === 'trabajador' && $customCode === '') {
                            $newWorkerCode = generateUniqueWorkerCode($pdo, (int)$targetId);
                        }

                        $stmt = $pdo->prepare("UPDATE usuarios SET worker_code = :worker_code WHERE id = :id");
                        $stmt->execute([
                            ':worker_code' => $newWorkerCode,
                            ':id' => $targetId
                        ]);
                        $msg = "<div class='alert alert-success'>Rol actualizado correctamente.</div>";
                    }
                }
            } elseif ($action === 'toggle_block') {
                if (!$supportsBloqueado) {
                    $msg = "<div class='alert alert-error'>Falta columna bloqueado en tabla usuarios.</div>";
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET bloqueado = CASE WHEN bloqueado = 1 THEN 0 ELSE 1 END WHERE id = :id");
                    $stmt->execute([':id' => $targetId]);
                    $msg = $stmt->rowCount() > 0
                        ? "<div class='alert alert-success'>Estado de bloqueo actualizado.</div>"
                        : "<div class='alert alert-error'>No se puede bloquear/desbloquear ese usuario.</div>";
                }
            }
        } catch (Exception $e) {
            $msg = "<div class='alert alert-error'>Error en la base de datos.</div>";
        }
    }
}

// — Dashboard stats —
$hasFechaHora = hasTableColumn($pdo, 'pedidos', 'fecha_hora');
$todayOrders = 0;
$todayRevenue = 0.0;
$pendingOrders = 0;
$preparingOrders = 0;
$activeOrders = [];
$activeOrderCount = 0;
$ingredientsTable = hasTableColumn($pdo, 'ingredientes', 'cantidad') && hasTableColumn($pdo, 'ingredientes', 'stock_minimo');
$redIngredients = 0;
$topSellingProduct = '-';
$productCount = 0;
$notificationsTable = hasTableColumn($pdo, 'notificaciones', 'leida');
$unreadNotifications = 0;

try {
    if ($hasFechaHora) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE DATE(fecha_hora) = CURDATE()");
        $todayOrders = (int)$stmt->fetchColumn();

        $stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE DATE(fecha_hora) = CURDATE()");
        $todayRevenue = (float)$stmt->fetchColumn();
    }

    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'");
    $pendingOrders = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'preparando'");
    $preparingOrders = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado IN ('pendiente', 'preparando', 'listo')");
    $activeOrderCount = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT id_pedido, estado, total, fecha_hora FROM pedidos WHERE estado IN ('pendiente', 'preparando') ORDER BY fecha_hora DESC LIMIT 10");
    $activeOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($ingredientsTable) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM ingredientes WHERE cantidad < stock_minimo");
        $redIngredients = (int)$stmt->fetchColumn();
    }

    $stmt = $pdo->query("SELECT COUNT(*) FROM productos");
    $productCount = (int)$stmt->fetchColumn();

    if ($productCount > 0) {
        $stmt = $pdo->query(
            "SELECT p.nombre, SUM(pi.cantidad) AS total_vendido " .
            "FROM pedido_items pi " .
            "JOIN pedidos pe ON pe.id_pedido = pi.id_pedido " .
            "JOIN productos p ON pi.id_producto = p.id " .
            "WHERE DATE(pe.fecha_hora) = CURDATE() " .
            "GROUP BY p.id, p.nombre " .
            "ORDER BY total_vendido DESC LIMIT 1"
        );
        $topProduct = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($topProduct) {
            $topSellingProduct = $topProduct['nombre'];
        }
    }

    if ($notificationsTable && isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario = :id_usuario AND leida = 0");
        $stmt->execute([':id_usuario' => $_SESSION['user_id']]);
        $unreadNotifications = (int)$stmt->fetchColumn();
    }
} catch (Exception $e) {
    $todayOrders = 0;
    $todayRevenue = 0.0;
    $pendingOrders = 0;
    $preparingOrders = 0;
    $activeOrders = [];
    $activeOrderCount = 0;
    $redIngredients = 0;
    $topSellingProduct = '-';
    $productCount = 0;
    $unreadNotifications = 0;
}

$sqlUsuarios = "SELECT id, nombre, email, worker_code" . ($supportsBloqueado ? ", bloqueado" : ", 0 AS bloqueado") . " FROM usuarios ORDER BY id";
$stmt = $pdo->query($sqlUsuarios);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administracion</title>
    <link rel="icon" type="image/png" href="assets/favicon.png">
    <link rel="shortcut icon" type="image/png" href="assets/favicon.png">
    <link rel="stylesheet" href="styles.css?v=20260317-1">
</head>
<body>
<?php
$display_name = trim($_SESSION['nombre'] ?? '');
if ($display_name === '') {
    $display_name = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? '');
}
?>
<header class="landing-header">
  <div class="landing-bar">
    <div class="profile-section">
      <button class="profile-btn" id="profileBtn">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="white">
          <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c1.52 0 5.1 1.34 5.1 5v1H6.9v-1c0-3.66 3.58-5 5.1-5z"/>
        </svg>
      </button>
      <span class="user-name"><?= htmlspecialchars($display_name) ?></span>
      <div class="dropdown" id="profileDropdown">
          <a href="perfil.php" data-i18n="nav.myProfile">Mi perfil</a>
          <a href="politica_cookies.php" class="open-cookie-preferences" data-i18n="nav.customizeCookies">Personalizar cookies</a>
          <a href="logout.php" data-i18n="nav.logout">Cerrar sesión</a>
        </div>
    </div>

    <a href="admin.php" class="landing-logo">
      <span class="landing-logo-text">Zyma</span>
    </a>

    <div class="landing-actions">
      <a href="admin_notifications.php" class="notif-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="white">
          <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6V11c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5S10.5 3.17 10.5 4v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
        </svg>
        <?php if ($notificationsTable && $unreadNotifications > 0): ?>
            <span class="notif-count"><?= $unreadNotifications ?></span>
        <?php endif; ?>
      </a>
      <div class="quick-menu-section">
        <button class="quick-menu-btn" id="quickBtn" aria-label="Menú rápido">
          <svg class="quick-menu-icon" viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
            <line x1="5" y1="7" x2="19" y2="7" />
            <line x1="5" y1="12" x2="19" y2="12" />
            <line x1="5" y1="17" x2="19" y2="17" />
          </svg>
        </button>
        <div class="dropdown quick-dropdown" id="quickDropdown">
          <a href="admin_orders.php">Pedidos</a>
          <a href="admin_inventory.php">Inventario</a>
          <a href="admin_products.php">Productos</a>
          <a href="admin_notifications.php">Notificaciones</a>
        </div>
      </div>
    </div>
  </div>
</header>

<script>
(function() {
    var btn = document.getElementById('profileBtn');
    if (btn) btn.onclick = function() {
        var d = document.getElementById('profileDropdown');
        if (d) d.classList.toggle('show');
    };
    btn = document.getElementById('quickBtn');
    if (btn) btn.onclick = function() {
        var d = document.getElementById('quickDropdown');
        if (d) d.classList.toggle('show');
    };
    document.addEventListener('click', function(e) {
        var pd = document.getElementById('profileDropdown');
        if (pd && !e.target.closest('.profile-section')) pd.classList.remove('show');
        var qd = document.getElementById('quickDropdown');
        if (qd && !e.target.closest('.quick-menu-section')) qd.classList.remove('show');
    });
})();
</script>

<div class="container">
    <?= $msg ?>

    <div class="section-card section-card-hero">
        <div class="row-between section-head">
            <div>
                <span class="admin-hero-kicker">Panel de control</span>
                <h2>Bienvenido, <?= htmlspecialchars(explode(' ', $display_name)[0]) ?></h2>
                <p class="lead">Resumen de pedidos, inventario y usuarios. Accede rápidamente a las secciones principales.</p>
            </div>
            <div class="action-links">
                <a href="admin_orders.php" class="landing-link">Pedidos</a>
                <a href="admin_inventory.php" class="landing-link">Inventario</a>
                <a href="admin_products.php" class="landing-link">Productos</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card stat-card-orders">
                <h3>Pedidos del día</h3>
                <p class="stat-number" id="todayOrdersValue"><?= $hasFechaHora ? $todayOrders : 'N/D' ?></p>
                <span class="stat-label"><?= $hasFechaHora ? 'Pedidos registrados hoy' : 'Fecha no disponible' ?></span>
            </div>
            <div class="stat-card stat-card-revenue">
                <h3>Ingresos del día</h3>
                <p class="stat-number" id="todayRevenueValue">€<?= number_format($todayRevenue, 2, ',', '.') ?></p>
                <span class="stat-label"><?= $hasFechaHora ? 'Ventas de hoy' : 'Fecha no disponible' ?></span>
            </div>
            <div class="stat-card stat-card-active">
                <h3>Pedidos activos</h3>
                <p class="stat-number" id="activeOrdersValue"><?= $activeOrderCount ?></p>
                <span class="stat-label">Pedidos en curso</span>
            </div>
            <div class="stat-card stat-card-preparing">
                <h3>En preparación</h3>
                <p class="stat-number"><?= $preparingOrders ?></p>
                <span class="stat-label">En proceso ahora</span>
            </div>
            <div class="stat-card stat-card-stock">
                <h3>Ingredientes en rojo</h3>
                <p class="stat-number" id="redIngredientsValue"><?= $ingredientsTable ? $redIngredients : 'N/D' ?></p>
                <span class="stat-label"><?= $ingredientsTable ? 'Inventario crítico' : 'Inventario no detectado' ?></span>
            </div>
            <div class="stat-card stat-card-top">
                <h3>Más vendido</h3>
                <p class="stat-number" id="topProductValue"><?= htmlspecialchars($topSellingProduct) ?></p>
                <span class="stat-label"><?= $productCount > 0 ? 'Producto estrella hoy' : 'Sin productos' ?></span>
            </div>
            <div class="stat-card stat-card-notif">
                <h3>Notificaciones</h3>
                <p class="stat-number" id="notificationsValue"><?= $notificationsTable ? $unreadNotifications : 'N/D' ?></p>
                <span class="stat-label"><?= $notificationsTable ? 'Sin leer' : 'No detectadas' ?></span>
            </div>
            <div class="stat-card stat-card-users">
                <h3>Usuarios</h3>
                <p class="stat-number"><?= count($usuarios) ?></p>
                <span class="stat-label">Registrados en la plataforma</span>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="row-between section-head">
            <h2>Pedidos en tiempo real</h2>
            <div class="section-head-badges">
                <span class="badge-status badge-estado-pendiente" id="liveBadge">● En vivo</span>
                <a href="admin_orders.php" class="landing-link">Gestionar pedidos →</a>
            </div>
        </div>
        <?php if (!empty($activeOrders)): ?>
            <div class="admin-table-wrap">
                <table class="admin-users-table table-compact table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activeOrders as $order): ?>
                        <tr>
                            <td><strong>#<?= (int)$order['id_pedido'] ?></strong></td>
                            <td>
                                <span class="badge-status badge-estado-<?= htmlspecialchars($order['estado'] ?? 'pendiente') ?>">
                                    <?= htmlspecialchars(ucfirst($order['estado'] ?? 'pendiente')) ?>
                                </span>
                            </td>
                            <td><strong>€<?= number_format((float)$order['total'], 2, ',', '.') ?></strong></td>
                            <td><?= htmlspecialchars($order['fecha_hora'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="empty-state">No hay pedidos activos disponibles.</p>
        <?php endif; ?>
    </div>
    <?php if (!$supportsBloqueado): ?>
      <div class="alert alert-error">Bloqueo no disponible. Ejecuta: ALTER TABLE usuarios ADD COLUMN bloqueado TINYINT(1) NOT NULL DEFAULT 0;</div>
    <?php endif; ?>

    <div class="section-card">
        <div class="row-between section-head">
            <h2>Añadir usuario</h2>
            <span class="badge-status badge-estado-listo">Nuevo</span>
        </div>
        <form method="POST" class="admin-add-form">
            <div class="admin-add-form-grid">
                <div>
                    <label for="add_nombre">Nombre</label>
                    <input type="text" name="nombre" id="add_nombre" placeholder="Nombre completo" required>
                </div>
                <div>
                    <label for="add_email">Email</label>
                    <input type="email" name="email" id="add_email" placeholder="correo@ejemplo.com" required>
                </div>
                <div>
                    <label for="add_password">Contraseña</label>
                    <input type="password" name="password" id="add_password" placeholder="Mínimo 6 caracteres" required>
                </div>
                <div>
                    <label for="add_role">Rol</label>
                    <select name="role" id="add_role" required>
                        <option value="cliente">Cliente</option>
                        <option value="trabajador">Trabajador</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div>
                    <label for="add_worker_code">Código de trabajador</label>
                    <input type="text" name="worker_code" id="add_worker_code" placeholder="Opcional">
                </div>
                <div class="admin-add-form-btn">
                    <button type="submit" name="add">Crear usuario</button>
                </div>
            </div>
        </form>
    </div>

    <div class="section-card">
        <div class="row-between section-head">
            <h2>Usuarios registrados</h2>
            <span class="badge-status badge-estado-info"><?= count($usuarios) ?> usuarios</span>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-users-table table-hover">
                <thead class="table-head-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Código</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <?php
                        $rol = roleFromWorkerCode($u['worker_code'] ?? null);
                        $isAdmin = ($u['worker_code'] ?? '') === 'ADMIN';
                        $isSelf = ((int)$u['id'] === (int)$_SESSION['user_id']);
                        $isBlocked = (int)($u['bloqueado'] ?? 0) === 1;
                        ?>
                        <tr>
                            <td><?= (int)$u['id'] ?></td>
                            <td><?= htmlspecialchars($u['nombre'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($rol)) ?></td>
                            <td><?= htmlspecialchars(($u['worker_code'] ?? '') !== '' ? $u['worker_code'] : '-') ?></td>
                            <td>
                                <span class="badge-status <?= $isBlocked ? 'badge-estado-cancelado' : 'badge-estado-listo' ?>">
                                    <?= $isBlocked ? 'Bloqueado' : 'Activo' ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!$isSelf): ?>
                                    <div class="admin-action-row">
                                        <form method="POST" class="admin-action-form admin-role-form">
                                            <input type="hidden" name="action" value="role">
                                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                            <select name="new_role">
                                                <option value="cliente" <?= $rol === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                                                <option value="trabajador" <?= $rol === 'trabajador' ? 'selected' : '' ?>>Trabajador</option>
                                                <option value="admin" <?= $rol === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                            <input type="text" name="new_worker_code" placeholder="Código opcional">
                                            <button type="submit">Guardar rol</button>
                                        </form>

                                        <form method="POST" class="admin-action-form">
                                            <input type="hidden" name="action" value="toggle_block">
                                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                            <button type="submit" <?= !$supportsBloqueado ? 'disabled' : '' ?>>
                                                <?= $isBlocked ? 'Desbloquear' : 'Bloquear' ?>
                                            </button>
                                        </form>

                                        <form method="POST" class="admin-action-form" onsubmit="return confirm('Eliminar usuario?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                            <button type="submit">Eliminar</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="admin-self-label">Tu usuario</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<footer>
    <p>&copy; 2026 Zyma. Todos los derechos reservados.</p>
    <p class="footer-legal-links">
        <a href="politica_cookies.php">Política de Cookies</a>
        <span>|</span>
        <a href="politica_privacidad.php">Política de Privacidad</a>
        <span>|</span>
        <a href="aviso_legal.php">Aviso Legal</a>
    </p>
</footer>

<?php include 'cookie_popup.php'; ?>

<script>
async function refreshDashboardStats() {
  try {
    const response = await fetch('admin_dashboard_stats.php');
    if (!response.ok) {
      return;
    }
    const data = await response.json();
    if (data.success && data.data) {
      const stats = data.data;
      document.getElementById('todayOrdersValue')?.textContent = stats.today_orders;
      document.getElementById('todayRevenueValue')?.textContent = '€' + parseFloat(stats.today_sales).toFixed(2).replace('.', ',');
      document.getElementById('activeOrdersValue')?.textContent = stats.active_orders;
      document.getElementById('redIngredientsValue')?.textContent = stats.red_ingredients;
      document.getElementById('topProductValue')?.textContent = stats.top_product_of_day || '-';
    }
  } catch (error) {
    console.error('Error al actualizar estadísticas:', error);
  }
}

setInterval(refreshDashboardStats, 30000);
</script>
</body>
</html>

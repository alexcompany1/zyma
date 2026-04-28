<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

session_start();
require_once 'config.php';
require_once 'admin_helpers.php';

requireRoles(['admin', 'trabajador']);

createAdminSchema($pdo);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $orderId = intval($_POST['order_id'] ?? 0);
    if ($orderId > 0) {
        try {
            if ($_POST['action'] === 'update_status') {
                $newStatus = trim($_POST['status'] ?? '');
                if (in_array($newStatus, Order::allStatuses(), true)) {
                    changeOrderStatus($pdo, $orderId, $newStatus, (int)$_SESSION['user_id']);
                    $message = "Estado actualizado correctamente.";
                } else {
                    $message = "Estado inválido.";
                }
            }
            if ($_POST['action'] === 'advance') {
                $current = getOrderCurrentStatus($pdo, $orderId);
                $nextStatus = Order::nextStatus($current ?: Order::STATUS_PENDING);
                if ($nextStatus !== $current) {
                    changeOrderStatus($pdo, $orderId, $nextStatus, (int)$_SESSION['user_id']);
                    $message = "Pedido avanzado a " . Order::statusLabel($nextStatus) . ".";
                } else {
                    $message = "El pedido ya está en el último estado.";
                }
            }
        } catch (Exception $e) {
            $message = "No se pudo cambiar el estado: " . htmlspecialchars($e->getMessage());
        }
    }
}

$notesColumn = hasTableColumn($pdo, 'pedidos', 'notas_cliente')
    ? 'COALESCE(p.notas_cliente, "") AS notes,'
    : '"" AS notes,';

$createdAtColumn = hasTableColumn($pdo, 'pedidos', 'created_at')
    ? 'p.created_at'
    : 'p.fecha_hora';

try {
    $stmt = $pdo->query(
        "SELECT p.id_pedido, p.id_usuario, p.estado, p.total, {$notesColumn} p.fecha_hora AS order_date, u.nombre AS user_name, u.email AS user_email
         FROM pedidos p
         LEFT JOIN usuarios u ON p.id_usuario = u.id
         ORDER BY p.id_pedido DESC"
    );
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $orderIds = array_column($orders, 'id_pedido');
    $itemsByOrder = [];
    if (!empty($orderIds)) {
        $in = implode(',', array_map('intval', $orderIds));
        $stmt = $pdo->query(
            "SELECT pi.id_pedido, pi.id_producto, pi.cantidad, pi.precio_unitario, pr.nombre AS product_name
             FROM pedido_items pi
             LEFT JOIN productos pr ON pr.id = pi.id_producto
             WHERE pi.id_pedido IN ($in)
             ORDER BY pi.id_pedido, pi.id_producto"
        );
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($items as $item) {
            $itemsByOrder[$item['id_pedido']][] = $item;
        }
    }
} catch (Exception $e) {
    $orders = [];
    $itemsByOrder = [];
    $message = 'Error al cargar los pedidos: ' . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Pedidos</title>
<link rel="icon" type="image/png" href="assets/favicon.png">
<link rel="stylesheet" href="styles.css?v=20260211-5">
<style>
.card-grid { display: grid; gap: 1.2rem; }
.order-card { border: 1px solid #ddd; border-radius: 12px; padding: 1.2rem; background:#fff; box-shadow:0 8px 18px rgba(0,0,0,.05); }
.order-card h3 { margin-bottom: 0.4rem; }
.order-meta { display:flex; flex-wrap:wrap; gap:0.75rem; align-items:center; margin-bottom:1rem; }
.order-meta span { font-size:.95rem; color:#555; }
.order-items { margin: .8rem 0; }
.order-items li { margin-bottom: .4rem; }
.form-inline { display:flex; gap:.75rem; flex-wrap:wrap; align-items:center; }
.form-inline button { white-space:nowrap; }
.badge-small { padding: .3rem .7rem; border-radius: 999px; font-size:.8rem; }
</style>
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
      <div class="dropdown" id="dropdownMenu">
          <a href="perfil.php">Mi perfil</a>
          <a href="politica_cookies.php" class="open-cookie-preferences">Personalizar cookies</a>
          <a href="logout.php">Cerrar Sesión</a>
      </div>
    </div>
    <a href="admin.php" class="landing-logo"><span class="landing-logo-text">Zyma</span></a>
        <div class="quick-menu-section">
      <button class="quick-menu-btn" id="quickMenuBtn" aria-label="Menú rápido"></button>
      <div class="dropdown quick-dropdown" id="quickDropdown">
        <a href="admin.php">Panel Admin</a>
        <a href="admin_orders.php">Pedidos</a>
        <a href="admin_inventory.php">Inventario</a>
        <a href="admin_products.php">Productos</a>
      </div>
    </div>
  </div>
</header>

<div class="container">
    <div class="section-card">
        <div class="row-between section-head">
            <div>
                <h2>Pedidos activos</h2>
                <p class="lead">Gestiona el flujo de pedidos en un solo lugar.</p>
            </div>
            <div>
                <a href="usuario.php" class="landing-link">Volver a inicio</a>
            </div>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <p class="empty-state">No hay pedidos en este momento.</p>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($orders as $order): ?>
                    <?php $statusClass = Order::statusColor($order['estado'] ?? Order::STATUS_PENDING); ?>
                    <div class="order-card">
                        <div class="order-meta">
                            <span><strong>#<?= (int)$order['id_pedido'] ?></strong></span>
                            <span class="badge-status <?= htmlspecialchars($statusClass) ?> badge-small"><?= Order::statusLabel($order['estado'] ?? Order::STATUS_PENDING) ?></span>
                            <span>Cliente: <?= htmlspecialchars($order['user_name'] ?: $order['user_email'] ?: 'Anonimo') ?></span>
                            <span>Total: €<?= number_format((float)$order['total'], 2, ',', '.') ?></span>
                            <span>Fecha: <?= htmlspecialchars($order['order_date']) ?></span>
                        </div>
                        <p><strong>Productos:</strong></p>
                        <ul class="order-items">
                            <?php foreach ($itemsByOrder[$order['id_pedido']] ?? [] as $item): ?>
                                <li><?= htmlspecialchars($item['product_name'] ?: 'Producto desconocido') ?> — <?= (float)$item['cantidad'] ?> x €<?= number_format((float)$item['precio_unitario'], 2, ',', '.') ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if (!empty($order['notes'])): ?>
                            <p><strong>Notas:</strong> <?= htmlspecialchars($order['notes']) ?></p>
                        <?php endif; ?>
                        <form method="POST" class="form-inline">
                            <input type="hidden" name="order_id" value="<?= (int)$order['id_pedido'] ?>">
                            <input type="hidden" name="action" value="update_status">
                            <label for="status-<?= (int)$order['id_pedido'] ?>">Cambiar estado:</label>
                            <select name="status" id="status-<?= (int)$order['id_pedido'] ?>">
                                <?php foreach (Order::allStatuses() as $statusOption): ?>
                                    <option value="<?= htmlspecialchars($statusOption) ?>" <?= $statusOption === $order['estado'] ? 'selected' : '' ?>><?= Order::statusLabel($statusOption) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn-add-cart">Guardar</button>
                            <?php if (($order['estado'] ?? '') !== Order::STATUS_DELIVERED): ?>
                                <button type="submit" name="action" value="advance" class="btn-add-cart">Avanzar</button>
                            <?php endif; ?>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
const profileBtn = document.getElementById('profileBtn');
const dropdownMenu = document.getElementById('dropdownMenu');
if (profileBtn && dropdownMenu) {
    profileBtn.addEventListener('click', () => dropdownMenu.classList.toggle('show'));
    window.addEventListener('click', e => {
        if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.remove('show');
        }
    });
}
</script>
</body>
</html>

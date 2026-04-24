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
    $action = $_POST['action'];
    $userId = (int)$_SESSION['user_id'];

    if ($action === 'read_single' && isset($_POST['notification_id'])) {
        markNotificationRead($pdo, (int)$_POST['notification_id'], $userId);
        $message = 'Notificación marcada como leída.';
    }

    if ($action === 'read_all') {
        markAllNotificationsRead($pdo, $userId);
        $message = 'Todas las notificaciones se marcaron como leídas.';
    }
}

$notifications = getRecentNotifications($pdo, (int)$_SESSION['user_id'], 20);
$unreadCount = getUnreadNotificationsCount($pdo, (int)$_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Notificaciones</title>
<link rel="icon" type="image/png" href="assets/favicon.png">
<link rel="stylesheet" href="styles.css?v=20260211-5">
<style>
.notifications-list { display:grid; gap:1rem; }
.notification-card { border:1px solid #ddd; border-radius:12px; padding:1rem; background:#fff; }
.notification-unread { background:#f7fbff; }
.notification-card strong { display:block; margin-bottom:.5rem; }
.notification-card small { color:#666; }
.notification-actions { display:flex; gap:.75rem; flex-wrap:wrap; margin-top:1rem; }
.notification-actions form { display:inline-block; }
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
                <h2>Notificaciones</h2>
                <p class="lead">Tienes <?= $unreadCount ?> notificación<?= $unreadCount === 1 ? '' : 'es' ?> sin leer.</p>
            </div>
            <div>
                <a href="usuario.php" class="landing-link">Volver a inicio</a>
            </div>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <div class="notification-actions">
            <form method="POST">
                <input type="hidden" name="action" value="read_all">
                <button type="submit" class="btn-add-cart">Marcar todas como leídas</button>
            </form>
        </div>
        <div class="notifications-list">
            <?php if (empty($notifications)): ?>
                <div class="notification-card empty-state">No hay notificaciones aún.</div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-card <?= $notification['leida'] ? 'notification-read' : 'notification-unread' ?>">
                        <strong><?= htmlspecialchars($notification['titulo'] ?: 'Notificación') ?></strong>
                        <p><?= htmlspecialchars($notification['mensaje'] ?? '') ?></p>
                        <small><?= htmlspecialchars(date('d/m/Y H:i', strtotime($notification['fecha']))) ?></small>
                        <?php if (!$notification['leida']): ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="read_single">
                                <input type="hidden" name="notification_id" value="<?= (int)$notification['id'] ?>">
                                <button type="submit" class="btn-add-cart">Marcar leída</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
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

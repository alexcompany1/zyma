<?php
/**
 * header.php
 * Cabecera unificada para paginas con sesion.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = !empty($_SESSION['user_id']);
$display_name = '';
if ($is_logged_in) {
    $display_name = trim($_SESSION['nombre'] ?? '');
    if ($display_name === '') {
        $display_name = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? '');
    }
}

$show_cart = $show_cart ?? true;
$show_notif = $show_notif ?? false;
$home_link = $home_link ?? 'usuario.php';
$cart_count = count($_SESSION['cart'] ?? []);

$unread_notifications = null;
if ($show_notif && $is_logged_in) {
    try {
        require_once 'config.php';
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario = :id_usuario AND leida = 0");
        $stmt->execute([':id_usuario' => $_SESSION['user_id'] ?? 0]);
        $unread_notifications = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        $unread_notifications = 0;
    }
}
?>

<header class="landing-header unified-header">
  <div class="landing-bar">
    <?php if ($is_logged_in): ?>
      <div class="profile-section">
        <button class="profile-btn" id="profileBtn">
          <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="white">
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c1.52 0 5.1 1.34 5.1 5v1H6.9v-1c0-3.66 3.58-5 5.1-5z"/>
          </svg>
        </button>
        <span class="user-name"><?= htmlspecialchars($display_name) ?></span>
        <div class="dropdown" id="dropdownMenu">
          <a href="perfil.php">Mi perfil</a>
          <a href="logout.php">Cerrar sesion</a>
        </div>
      </div>
    <?php endif; ?>

    <a href="<?= htmlspecialchars($home_link) ?>" class="landing-logo">
      <span class="landing-logo-text">Zyma</span>
    </a>

    <div class="landing-actions">
      <?php if (!$is_logged_in): ?>
        <a href="login.php" class="landing-link">Entrar</a>
        <a href="registro.php" class="landing-cta">Crear cuenta</a>
      <?php endif; ?>

      <?php if ($is_logged_in): ?>
        <div class="quick-menu-section">
          <button class="quick-menu-btn" id="quickMenuBtn" aria-label="Menu rapido"></button>
          <div class="dropdown quick-dropdown" id="quickDropdown">
            <a href="usuario.php">Inicio</a>
            <a href="carta.php">Ver carta</a>
            <a href="valoraciones.php">Valoraciones</a>
            <a href="tickets.php">Tickets</a>
          </div>
        </div>
      <?php endif; ?>

      <div class="cart-section">
        <?php if ($show_notif): ?>
          <a href="notificaciones.php" class="notif-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="white">
              <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6V11c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5S10.5 3.17 10.5 4v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
            </svg>
            <?php if (!empty($unread_notifications)): ?>
              <span class="notif-count"><?= $unread_notifications ?></span>
            <?php endif; ?>
          </a>
        <?php endif; ?>

        <?php if ($show_cart && $is_logged_in): ?>
          <a href="carrito.php" class="cart-btn">
            <img src="assets/cart-icon.png" alt="Carrito">
            <span class="cart-count"><?= $cart_count ?></span>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<script>
  const profileBtn = document.getElementById('profileBtn');
  const dropdownMenu = document.getElementById('dropdownMenu');

  profileBtn?.addEventListener('click', () => {
    dropdownMenu.classList.toggle('show');
  });

  window.addEventListener('click', (e) => {
    if (!profileBtn?.contains(e.target) && !dropdownMenu?.contains(e.target)) {
      dropdownMenu.classList.remove('show');
    }
  });
</script>

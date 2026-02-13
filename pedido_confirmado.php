<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
?>
<?php
/**
 * pedido_confirmado.php
 * Mensaje de confirmacion de pedido.
 */

session_start();

if (!isset($_SESSION['mensaje_pedido'])) {
    header('Location: carrito.php');
    exit;
}

$mensaje = $_SESSION['mensaje_pedido'];
unset($_SESSION['mensaje_pedido']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zyma - Pedido Confirmado</title>
<link rel="stylesheet" href="styles.css?v=20260211-5">
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
          <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4 1.79 4 4 4zm0 2c1.52 0 5.1 1.34 5.1 5v1H6.9v-1c0-3.66 3.58-5 5.1-5z"/>
        </svg>
      </button>
      <span class="user-name"><?= htmlspecialchars($display_name) ?></span>
      <div class="dropdown" id="dropdownMenu">
          <a href="perfil.php">Mi perfil</a>
          <a href="logout.php">Cerrar sesion</a>
        </div>
    </div>
    <a href="usuario.php" class="landing-logo">
      <span class="landing-logo-text">Zyma</span>
    </a>
        <div class="quick-menu-section">
      <button class="quick-menu-btn" id="quickMenuBtn" aria-label="Menu rapido"></button>
      <div class="dropdown quick-dropdown" id="quickDropdown">
        <a href="usuario.php">Inicio</a>
        <a href="carta.php">Ver carta</a>
      </div>
    </div>
    <div class="cart-section">
      <a href="carrito.php" class="cart-btn">
        <img src="assets/cart-icon.png" alt="Carrito">
        <span class="cart-count"><?= count($_SESSION['cart'] ?? []) ?></span>
      </a>
    </div>
  </div>
</header>

<div class="container">
    <div class="main-content">
        <div class="center">
            <h1 class="welcome">¡Pedido Confirmado!</h1>
            <p class="muted lead mt-2 mb-3">
                <?= htmlspecialchars($mensaje) ?>
            </p>
            <p class="mb-3">
                Nuestro equipo está preparando tu pedido. ¡Gracias por confiar en Zyma!
            </p>
            <div class="btn-row center">
                <a href="mis_pedidos.php" class="btn-cart">Ver Mis Pedidos</a>
                <a href="carta.php" class="btn-seguir-comprando">Seguir Comprando</a>
            </div>
        </div>
    </div>
</div>

<script>
const profileBtn = document.getElementById('profileBtn');
const dropdownMenu = document.getElementById('dropdownMenu');
profileBtn.addEventListener('click', () => dropdownMenu.classList.toggle('show'));
window.addEventListener('click', e => {
    if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove('show');
    }
});
</script>
<script src="assets/mobile-header.js?v=20260211-6"></script>
</body>
</html>

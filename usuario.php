<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$display_name = trim($_SESSION['nombre'] ?? '');
if ($display_name === '') {
    $display_name = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? '');
}
$cart_count = count($_SESSION['cart'] ?? []);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zyma - Restaurante de Hotdogs</title>
  <link rel="stylesheet" href="styles.css?v=20260211-5">
</head>
<body>
<header class="landing-header">
  <div class="landing-bar">
    <div class="profile-section">
      <button class="profile-btn" id="profileBtn" aria-label="Perfil">
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

    <a href="usuario.php" class="landing-logo">
      <span class="landing-logo-text">Zyma</span>
    </a>

        <div class="quick-menu-section">
        <button class="quick-menu-btn" id="quickMenuBtn" aria-label="Menu rapido"></button>
        <div class="dropdown quick-dropdown" id="quickDropdown">
          <a href="usuario.php">Inicio</a>
          <a href="carta.php">Ver carta</a>
          <a href="tickets.php">Tickets</a>
        </div>
      </div>
    <div class="cart-section">
      <a href="carrito.php" class="cart-btn" aria-label="Carrito">
        <img src="assets/cart-icon.png" alt="Carrito">
        <span class="cart-count"><?= $cart_count ?></span>
      </a>
    </div>
  </div>
</header>

<main class="main-content">
  <div class="center">
    <h1 class="welcome">&iexcl;Hola, <?= htmlspecialchars($display_name) ?>!</h1>
    <p class="muted mt-2 mb-3 lead">
      Bienvenido a Zyma. Aqu&iacute; cada bocado tiene historia: pan artesanal, ingredientes frescos y recetas de la casa.
    </p>
    <div class="btn-row center">
      <a href="carta.php" class="btn-cart">Ver la Carta</a>
    </div>
  </div>
</main>

<footer>
  <p>&copy; 2025 Zyma. Todos los derechos reservados.</p>
</footer>

<script>
const profileBtn = document.getElementById('profileBtn');
const dropdownMenu = document.getElementById('dropdownMenu');
if (profileBtn && dropdownMenu) {
  profileBtn.addEventListener('click', () => dropdownMenu.classList.toggle('show'));
  window.addEventListener('click', (e) => {
    if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
      dropdownMenu.classList.remove('show');
    }
  });
}
</script>
<script src="assets/mobile-header.js?v=20260211-6"></script>
</body>
</html>

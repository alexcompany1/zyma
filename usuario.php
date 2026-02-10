<?php
/**
 * usuario.php
 * Inicio de usuario.
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zyma - Restaurante de Hotdogs</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
  <!-- Perfil -->
  <div class="profile-section">
    <button class="profile-btn" id="profileBtn">
      <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="white">
        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c1.52 0 5.1 1.34 5.1 5v1H6.9v-1c0-3.66 3.58-5 5.1-5z"/>
      </svg>
    </button>
    <div class="dropdown" id="dropdownMenu">
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </div>
  
  <!-- Logo -->
  <div class="header-content">
    <a href="usuario.php" class="logo">Zyma</a>
  </div>
  
  <!-- Carrito -->
  <div class="cart-section">
    <a href="carrito.php" class="cart-btn">
      <img src="assets/cart-icon.png" alt="Carrito">
      <span class="cart-count">0</span>
    </a>
  </div>
</header>

<main class="main-content">
  <div class="center">
    <h1 class="welcome">¡Hola, <?= htmlspecialchars($_SESSION['email']) ?>!</h1>
    <p class="muted mt-2 mb-3 lead">
      Bienvenido a Zyma. Aquí cada bocado tiene historia: pan artesanal, ingredientes frescos y recetas de la casa.
    </p>
    <div class="btn-row center">
      <a href="carta.php" class="btn-cart">Ver la Carta</a>
      <a href="carrito.php" class="btn-seguir-comprando">Ir al Carrito</a>
    </div>
  </div>
</main>

<footer>
  <p>© 2025 Zyma. Todos los derechos reservados.</p>
</footer>

<script>
  // Menu perfil
  const profileBtn = document.getElementById('profileBtn');
  const dropdownMenu = document.getElementById('dropdownMenu');

  profileBtn.addEventListener('click', () => {
    dropdownMenu.classList.toggle('show');
  });

  window.addEventListener('click', (e) => {
    if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
      dropdownMenu.classList.remove('show');
    }
  });
</script>
</body>
</html>



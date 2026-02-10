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
<link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
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
    <div class="header-content"><a href="usuario.php" class="logo">Zyma</a></div>
    <div class="cart-section">
        <a href="carrito.php" class="cart-btn">
            <img src="assets/cart-icon.png" alt="Carrito">
            <span class="cart-count">0</span>
        </a>
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
</body>
</html>

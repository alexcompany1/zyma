<?php
/**
 * trabajador.php
 * Panel de trabajador.
 */

session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['worker_code']) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zyma - Trabajador</title>
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
    <div class="header-content">
        <a href="usuario.php" class="logo">Zyma</a>
    </div>
    <div class="cart-section">
        <a href="carrito.php" class="cart-btn">
            <img src="assets/cart-icon.png" alt="Carrito">
            <span class="cart-count"><?= count($_SESSION['cart'] ?? []) ?></span>
        </a>
    </div>
</header>

<div class="container">
    <div class="main-content">
        <h2 class="welcome">Bienvenido, <?= htmlspecialchars($_SESSION['email']) ?></h2>
        <p class="muted mb-3">Tienes acceso a funciones especiales como trabajador.</p>

        <div class="panel-box">
            <h3 class="panel-title">Panel de Control</h3>
            <ul class="list-clean">
                <li>
                    <a href="estadisticas.php" class="panel-link"> Ver estadísticas</a>
                </li>
                <li>
                    <a href="editar_carta.php" class="panel-link"> Editar carta</a>
                </li>
                <li>
                    <a href="gestionar_pedidos.php" class="panel-link"> Gestionar pedidos</a>
                </li>
            </ul>
        </div>
    </div>
</div>

<footer>
    <p>© 2025 Zyma. Todos los derechos reservados.</p>
</footer>

<script>
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

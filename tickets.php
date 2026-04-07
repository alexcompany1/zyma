<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

session_start();
require_once 'config.php';
require_once 'auth.php';
zymaRequireRole('client');

$pedidos = [];

try {
    $stmt = $pdo->prepare("
        SELECT id_pedido, total, fecha_hora, estado
        FROM pedidos
        WHERE id_usuario = :usuario_id
        ORDER BY fecha_hora DESC
    ");
    $stmt->execute([':usuario_id' => $_SESSION['user_id']]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    $pedidos = [];
}

$display_name = trim($_SESSION['nombre'] ?? '');
if ($display_name === '') {
    $display_name = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? '');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets de Compra - Zyma</title>
    <link rel="icon" type="image/png" href="assets/favicon.png">
    <link rel="stylesheet" href="styles.css?v=20260320-3">
</head>
<body>
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
        <a href="politica_cookies.php" class="open-cookie-preferences">Personalizar cookies</a>
        <a href="logout.php">Cerrar sesión</a>
      </div>
    </div>

    <a href="usuario.php" class="landing-logo">
      <span class="landing-logo-text">Zyma</span>
    </a>

    <div class="quick-menu-section">
      <button class="quick-menu-btn" id="quickMenuBtn" aria-label="Menú rápido"></button>
      <div class="dropdown quick-dropdown" id="quickDropdown">
        <a href="usuario.php">Inicio</a>
        <a href="carta.php">Ver carta</a>
        <a href="valoraciones.php">Valoraciones</a>
        <a href="incidencias.php">Incidencias</a>
        <a href="tickets.php">Tickets de compra</a>
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

<main class="container support-page">
    <section class="support-hero">
        <div class="support-hero-copy">
            <span class="support-kicker">Tus compras</span>
            <h1>Tickets de compra</h1>
            <p>Comprobantes de tus pedidos y facturas disponibles para descargar en cualquier momento.</p>
        </div>
    </section>

    <section class="profile-card support-purchases-card">
        <div class="profile-card-header">
            <span class="profile-section-kicker">Historial</span>
            <h2>Mis tickets de compra</h2>
            <p>Accede a los detalles de cada pedido y descarga tus comprobantes.</p>
        </div>

        <?php if (empty($pedidos)): ?>
            <p class="empty-state">Aún no tienes pedidos registrados.</p>
            <div class="btn-row center">
                <a href="carta.php" class="btn-cart">Hacer pedido</a>
            </div>
        <?php else: ?>
            <div class="support-orders-grid">
                <?php foreach ($pedidos as $p): ?>
                    <article class="support-order-card">
                        <h3>Pedido #<?= htmlspecialchars($p['id_pedido']) ?></h3>
                        <p>Fecha: <?= date('d/m/Y H:i', strtotime($p['fecha_hora'])) ?></p>
                        <p>Estado: <?= htmlspecialchars(ucfirst($p['estado'])) ?></p>
                        <p class="summary-total">Total: <?= number_format((float) $p['total'], 2, ',', '.') ?> EUR</p>
                        <a class="btn-cart" href="ticket.php?id=<?= urlencode((string) $p['id_pedido']) ?>">Ver ticket</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <div class="support-bottom-nav">
        <p>¿Tienes un problema con tu pedido? <a href="incidencias.php">Abrir incidencia</a></p>
    </div>
</main>

<footer>
    <p>&copy; 2025 Zyma. Todos los derechos reservados.</p>
    <p class="footer-legal-links">
        <a href="politica_cookies.php">Política de Cookies</a>
        <span>|</span>
        <a href="politica_privacidad.php">Política de Privacidad</a>
        <span>|</span>
        <a href="aviso_legal.php">Aviso Legal</a>
    </p>
</footer>

<script>
const profileBtn = document.getElementById('profileBtn');
const dropdownMenu = document.getElementById('dropdownMenu');
if (profileBtn && dropdownMenu) {
  profileBtn.addEventListener('click', () => {
    dropdownMenu.classList.toggle('show');
  });

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

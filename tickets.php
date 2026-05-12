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
        <a href="perfil.php" data-i18n="nav.myProfile">Mi perfil</a>
        <a href="politica_cookies.php" class="open-cookie-preferences" data-i18n="nav.customizeCookies">Personalizar cookies</a>
        <a href="logout.php" data-i18n="nav.logout">Cerrar sesión</a>
      </div>
    </div>

    <a href="usuario.php" class="landing-logo">
      <span class="landing-logo-text">Zyma</span>
    </a>

    <div class="quick-menu-section">
      <button class="quick-menu-btn" id="quickMenuBtn" data-i18n-aria="nav.quickMenu" aria-label="Menú rápido">
          <svg class="quick-menu-icon" viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 7h14M5 12h14M5 17h14" />
          </svg>
        </button>
      <div class="dropdown quick-dropdown" id="quickDropdown">
        <a href="usuario.php" data-i18n="nav.home">Inicio</a>
        <a href="carta.php" data-i18n="nav.viewMenu">Ver carta</a>
        <a href="valoraciones.php" data-i18n="nav.reviews">Valoraciones</a>
        <a href="incidencias.php" data-i18n="nav.incidents">Incidencias</a>
        <a href="tickets.php" data-i18n="nav.tickets">Tickets de compra</a>
      </div>
    </div>

    <div class="cart-section">
      <a href="carrito.php" class="cart-btn" data-i18n-aria="nav.cart" aria-label="Carrito">
        <img src="assets/cart-icon.png" alt="Carrito">
        <span class="cart-count"><?= zymaCartTotalItems() ?></span>
      </a>
    </div>
  </div>
</header>

<main class="container support-page">
    <section class="support-hero">
        <div class="support-hero-copy">
            <span class="support-kicker" data-i18n="tickets.heroKicker">Tus compras</span>
            <h1 data-i18n="tickets.myPurchasesTitle">Mis tickets de compra</h1>
            <p data-i18n="tickets.myPurchasesDesc">Accede a los detalles de cada pedido y descarga tus comprobantes.</p>
        </div>
    </section>

    <section class="profile-card support-purchases-card">
        <div class="profile-card-header">
            <span class="profile-section-kicker" data-i18n="tickets.historyKicker">Historial</span>
            <h2 data-i18n="tickets.myPurchasesTitle">Mis tickets de compra</h2>
            <p data-i18n="tickets.myPurchasesDesc">Accede a los detalles de cada pedido y descarga tus comprobantes.</p>
        </div>

        <?php if (empty($pedidos)): ?>
            <p class="empty-state" data-i18n="tickets.noOrders">Aún no tienes pedidos registrados.</p>
            <div class="btn-row center">
                <a href="carta.php" class="btn-cart" data-i18n="tickets.makeOrder">Hacer pedido</a>
            </div>
        <?php else: ?>
            <div class="support-orders-grid">
                <?php foreach ($pedidos as $p): ?>
                    <article class="support-order-card">
                        <h3>Pedido #<?= htmlspecialchars($p['id_pedido']) ?></h3>
                        <p><strong data-i18n="tickets.date">Fecha:</strong> <?= date('d/m/Y H:i', strtotime($p['fecha_hora'])) ?></p>
                        <p><strong data-i18n="tickets.statusLabel">Estado:</strong> <?= htmlspecialchars(ucfirst($p['estado'])) ?></p>
                        <p class="summary-total"><strong data-i18n="tickets.totalLabel">Total:</strong> €<?= number_format((float) $p['total'], 2, ',', '.') ?></p>
                        <a class="btn-cart" href="ticket.php?id=<?= urlencode((string) $p['id_pedido']) ?>" data-i18n="tickets.viewTicket">Ver ticket</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <div class="support-bottom-nav">
        <p>
            <span data-i18n="tickets.problemText">¿Tienes un problema con tu pedido?</span>
            <a href="incidencias.php" data-i18n="tickets.openIncidence">Abrir incidencia</a>
        </p>
    </div>
</main>

<footer>
    <p data-i18n="footer.rights">&copy; 2025 Zyma. Todos los derechos reservados.</p>
    <p class="footer-legal-links">
        <a href="politica_cookies.php" data-i18n="footer.cookiePolicy">Política de Cookies</a>
        <span>|</span>
        <a href="politica_privacidad.php" data-i18n="footer.privacy">Política de Privacidad</a>
        <span>|</span>
        <a href="aviso_legal.php" data-i18n="footer.legal">Aviso Legal</a>
    </p>
</footer>

<script>
const profileBtn = document.getElementById('profileBtn');
const dropdownMenu = document.getElementById('dropdownMenu');
const quickDropdown = document.getElementById('quickDropdown');
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
<script src="assets/lang.js?v=20260428-1"></script>
</body>
</html>



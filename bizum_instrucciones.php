<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

require_once 'config.php';
require_once 'payment_helpers.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$orderId = (int) ($_GET['id'] ?? 0);
if ($orderId <= 0) {
    header('Location: mis_pedidos.php');
    exit;
}

$order = zymaGetOrderForUser($pdo, $orderId, (int) $_SESSION['user_id']);
if (!$order) {
    header('Location: mis_pedidos.php');
    exit;
}

$displayName = trim($_SESSION['nombre'] ?? '');
if ($displayName === '') {
    $displayName = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? '');
}

$bizumPhone = BIZUM_PHONE !== '' ? BIZUM_PHONE : '[722 61 83 18]';
$bizumConcept = BIZUM_CONCEPT_PREFIX . ' #' . $orderId;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zyma - Instrucciones Bizum</title>
<link rel="icon" type="image/png" href="assets/favicon.png">
<link rel="stylesheet" href="styles.css?v=20260211-5">
<style>
.bizum-box{max-width:760px;margin:24px auto;padding:28px;border-radius:24px;background:#fff;box-shadow:0 18px 40px rgba(0,0,0,.08)}
.bizum-steps{margin:18px 0 0;padding-left:18px}
.bizum-steps li{margin-bottom:10px}
.bizum-callout{margin-top:16px;padding:16px;border-radius:16px;background:#f5fff3;border:1px solid rgba(60,112,57,.18)}
</style>
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
      <span class="user-name"><?= htmlspecialchars($displayName) ?></span>
      <div class="dropdown" id="dropdownMenu">
        <a href="perfil.php">Mi perfil</a>
        <a href="politica_cookies.php" class="open-cookie-preferences">Personalizar cookies</a>
        <a href="logout.php">Cerrar Sesion</a>
      </div>
    </div>
    <a href="usuario.php" class="landing-logo"><span class="landing-logo-text">Zyma</span></a>
    <div class="cart-section">
      <a href="carrito.php" class="cart-btn">
        <img src="assets/cart-icon.png" alt="Carrito">
        <span class="cart-count"><?= count($_SESSION['cart'] ?? []) ?></span>
      </a>
    </div>
  </div>
</header>

<div class="container">
  <div class="bizum-box">
    <h1 class="welcome">Completa tu pago por Bizum</h1>
    <p class="muted lead mt-2">Tu pedido #<?= (int) $orderId ?> ya se ha creado y esta pendiente de confirmacion.</p>

    <div class="bizum-callout">
      <p><strong>Importe:</strong> EUR <?= number_format((float) $order['total'], 2, ',', '.') ?></p>
      <p><strong>Enviar a:</strong> <?= htmlspecialchars(BIZUM_BENEFICIARY) ?> - <?= htmlspecialchars($bizumPhone) ?></p>
      <p><strong>Concepto:</strong> <?= htmlspecialchars($bizumConcept) ?></p>
      <p><strong>Estado actual:</strong> <?= htmlspecialchars((string) ($order['pago_estado'] ?? 'pendiente')) ?></p>
    </div>

    <ol class="bizum-steps">
      <li>Abre tu app bancaria y entra en la opcion de Bizum.</li>
      <li>Envianos el importe exacto del pedido al telefono indicado arriba.</li>
      <li>Escribe el concepto exactamente como aparece para poder localizar tu pago.</li>
      <li>Cuando lo completes, tu pedido seguira apareciendo en "Mis pedidos" mientras lo revisamos.</li>
    </ol>

    <?php if (BIZUM_PHONE === ''): ?>
      <p class="empty-state">Todavia no has configurado el telefono receptor de Bizum. Anade la variable BIZUM_PHONE en el servidor para mostrar el numero real.</p>
    <?php endif; ?>

    <div class="btn-row center mt-3">
      <a href="ticket.php?id=<?= (int) $orderId ?>" class="btn-cart">Ver ticket</a>
      <a href="mis_pedidos.php" class="btn-cart">Ver mis pedidos</a>
      <a href="usuario.php" class="btn-seguir-comprando">Volver al inicio</a>
    </div>
  </div>
</div>

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
<footer>
  <p>&copy; 2025 Zyma. Todos los derechos reservados.</p>
  <p class="footer-legal-links">
    <a href="politica_cookies.php">Politica de Cookies</a>
    <span>|</span>
    <a href="politica_privacidad.php">Politica de Privacidad</a>
    <span>|</span>
    <a href="aviso_legal.php">Aviso Legal</a>
  </p>
</footer>
</body>
</html>

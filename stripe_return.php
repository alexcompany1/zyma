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

$displayName = trim($_SESSION['nombre'] ?? '');
if ($displayName === '') {
    $displayName = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? '');
}

$title = 'Pago con tarjeta';
$message = '';
$error = null;
$orderId = 0;

try {
    if (isset($_GET['cancelled'], $_GET['order_id']) && $_GET['cancelled'] === '1') {
        $orderId = (int) $_GET['order_id'];
        if ($orderId <= 0) {
            throw new RuntimeException('No se ha encontrado el pedido cancelado.');
        }

        $order = zymaGetOrderForUser($pdo, $orderId, (int) $_SESSION['user_id']);
        if (!$order) {
            throw new RuntimeException('El pedido no pertenece a tu cuenta o ya no existe.');
        }

        zymaMarkPaymentStatus($pdo, $orderId, 'rechazado');
        zymaUpdateOrderStatus($pdo, $orderId, 'cancelado');

        $title = 'Pago cancelado';
        $message = 'Has cancelado el pago con tarjeta. Tu pedido no se ha confirmado y puedes volver al carrito para intentarlo de nuevo.';
    } else {
        $sessionId = trim($_GET['session_id'] ?? '');
        if ($sessionId === '') {
            throw new RuntimeException('No se ha recibido la sesion de Stripe.');
        }

        $session = zymaRetrieveStripeCheckoutSession($sessionId);
        $orderId = (int) ($session['client_reference_id'] ?? $session['metadata']['order_id'] ?? 0);
        if ($orderId <= 0) {
            throw new RuntimeException('Stripe no ha devuelto un pedido valido.');
        }

        $order = zymaGetOrderForUser($pdo, $orderId, (int) $_SESSION['user_id']);
        if (!$order) {
            throw new RuntimeException('El pedido no pertenece a tu cuenta o ya no existe.');
        }

        if (($session['payment_status'] ?? '') !== 'paid') {
            zymaMarkPaymentStatus($pdo, $orderId, 'rechazado');
            zymaUpdateOrderStatus($pdo, $orderId, 'cancelado');
            throw new RuntimeException('Stripe no ha confirmado el pago como completado.');
        }

        zymaMarkPaymentStatus($pdo, $orderId, 'pagado');
        zymaUpdateOrderStatus($pdo, $orderId, 'pendiente');
        zymaCreateOrderNotification($pdo, (int) $_SESSION['user_id'], $orderId, (float) $order['total']);
        unset($_SESSION['cart']);

        $title = 'Pago completado';
        $message = 'Stripe ha confirmado tu pago correctamente. Tu pedido ya esta registrado y listo para prepararse.';
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zyma - <?= htmlspecialchars($title) ?></title>
<link rel="icon" type="image/png" href="assets/favicon.png">
<link rel="stylesheet" href="styles.css?v=20260211-5">
<style>
.stripe-box{max-width:760px;margin:24px auto;padding:28px;border-radius:24px;background:#fff;box-shadow:0 18px 40px rgba(0,0,0,.08)}
.stripe-box.is-error{border:1px solid rgba(114,14,7,.18);background:#fff8f5}
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
  <div class="stripe-box<?= $error ? ' is-error' : '' ?>">
    <h1 class="welcome"><?= htmlspecialchars($title) ?></h1>

    <?php if ($error): ?>
      <p class="empty-state">No hemos podido cerrar el pago: <?= htmlspecialchars($error) ?></p>
      <div class="btn-row center mt-3">
        <a href="carrito.php" class="btn-cart">Volver al carrito</a>
        <a href="mis_pedidos.php" class="btn-seguir-comprando">Ver mis pedidos</a>
      </div>
    <?php else: ?>
      <p class="muted lead mt-2"><?= htmlspecialchars($message) ?></p>
      <?php if ($orderId > 0): ?>
        <p class="mt-2"><strong>Pedido:</strong> #<?= (int) $orderId ?></p>
      <?php endif; ?>
      <div class="btn-row center mt-3">
        <?php if ($orderId > 0): ?>
          <a href="ticket.php?id=<?= (int) $orderId ?>" class="btn-cart">Ver ticket</a>
        <?php endif; ?>
        <a href="mis_pedidos.php" class="btn-cart">Ver mis pedidos</a>
        <a href="usuario.php" class="btn-seguir-comprando">Volver al inicio</a>
      </div>
    <?php endif; ?>
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

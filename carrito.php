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
if (($_SESSION['worker_code'] ?? '') === 'ADMIN') {
    header('Location: admin.php');
    exit;
}

$products = [
    ['id' => 1, 'name' => 'Nachos con Queso', 'price' => 6.00, 'image' => 'assets/nachos.png'],
    ['id' => 2, 'name' => 'Patatas Fritas', 'price' => 3.50, 'image' => 'assets/fries.png'],
    ['id' => 3, 'name' => 'Hotdog BBQ', 'price' => 7.50, 'image' => 'assets/bbq_hotdog.png'],
    ['id' => 4, 'name' => 'Hotdog Clasico', 'price' => 5.99, 'image' => 'assets/hotdog.png'],
    ['id' => 5, 'name' => 'Hotdog Vegano', 'price' => 6.50, 'image' => 'assets/vegan-hotdog.png'],
    ['id' => 6, 'name' => 'Refresco Cola', 'price' => 2.00, 'image' => 'assets/soda.png'],
    ['id' => 7, 'name' => 'Agua Mineral', 'price' => 1.50, 'image' => 'assets/water.png'],
];

$cartItems = [];
$total = 0.0;

foreach ($_SESSION['cart'] ?? [] as $id => $qty) {
    foreach ($products as $product) {
        if ((int) $product['id'] === (int) $id) {
            $product['quantity'] = (int) $qty;
            $product['subtotal'] = (float) $product['price'] * (int) $qty;
            $cartItems[] = $product;
            $total += $product['subtotal'];
        }
    }
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pagar_online'])) {
    if (empty($cartItems)) {
        $error = 'No hay productos en el carrito.';
    } else {
        try {
            $metodoPago = $_POST['metodo_pago'] ?? '';
            if (!in_array($metodoPago, ['tarjeta', 'bizum'], true)) {
                throw new RuntimeException('Selecciona un metodo de pago valido.');
            }

            if ($metodoPago === 'tarjeta') {
                $order = zymaCreateOrderFromCart(
                    $pdo,
                    (int) $_SESSION['user_id'],
                    $cartItems,
                    'tarjeta',
                    'Pendiente de confirmacion por Stripe Checkout'
                );

                try {
                    $checkoutSession = zymaCreateStripeCheckoutSession($cartItems, (int) $order['order_id']);
                } catch (Throwable $stripeError) {
                    zymaMarkPaymentStatus($pdo, (int) $order['order_id'], 'rechazado');
                    zymaUpdateOrderStatus($pdo, (int) $order['order_id'], 'cancelado');
                    throw $stripeError;
                }

                header('Location: ' . $checkoutSession['url']);
                exit;
            }

            $telefonoBizum = preg_replace('/\s+/', '', $_POST['telefono_bizum'] ?? '');
            if ($telefonoBizum !== '' && !preg_match('/^(\+34)?[6789]\d{8}$/', $telefonoBizum)) {
                throw new RuntimeException('El telefono de Bizum no es valido.');
            }

            $bizumNotes = 'Bizum pendiente';
            if ($telefonoBizum !== '') {
                $bizumNotes .= ' - telefono cliente: ' . $telefonoBizum;
            }

            $order = zymaCreateOrderFromCart(
                $pdo,
                (int) $_SESSION['user_id'],
                $cartItems,
                'bizum',
                $bizumNotes
            );
            zymaCreateOrderNotification($pdo, (int) $_SESSION['user_id'], (int) $order['order_id'], (float) $order['total']);

            unset($_SESSION['cart']);
            header('Location: bizum_instrucciones.php?id=' . (int) $order['order_id']);
            exit;
        } catch (Throwable $e) {
            $error = 'Error al procesar el pago: ' . $e->getMessage();
        }
    }
}

$displayName = trim($_SESSION['nombre'] ?? '');
if ($displayName === '') {
    $displayName = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? '');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zyma - Tu Carrito</title>
<link rel="icon" type="image/png" href="assets/favicon.png">
<link rel="stylesheet" href="styles.css?v=20260211-5">
<style>
.payment-methods{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin:14px 0 18px}
.payment-option{position:relative;display:block;cursor:pointer}
.payment-option input{position:absolute;opacity:0;pointer-events:none}
.payment-option-card{display:flex;align-items:center;gap:14px;padding:16px 18px;border-radius:20px;border:1px solid rgba(114,14,7,.14);background:linear-gradient(135deg,#fffaf5,#fff2e7);box-shadow:0 10px 24px rgba(69,5,12,.07);transition:transform .18s ease,border-color .18s ease,box-shadow .18s ease,background .18s ease}
.payment-option-card::before{content:"";width:18px;height:18px;border-radius:999px;border:2px solid rgba(114,14,7,.45);box-sizing:border-box;flex:0 0 auto;background:#fff}
.payment-option-card strong{display:block;font-size:1.05rem;color:#45050C}
.payment-option-card span{display:block;color:rgba(69,5,12,.72);font-size:.95rem;line-height:1.35}
.payment-option-card.is-bizum{background:linear-gradient(135deg,#f6fff4,#eef9ea)}
.payment-option input:checked + .payment-option-card{border-color:#8f1251;box-shadow:0 16px 30px rgba(143,18,81,.18);transform:translateY(-2px)}
.payment-option input:checked + .payment-option-card::before{border-color:#8f1251;background:radial-gradient(circle,#c30c9f 0 45%,#fff 48% 100%)}
.payment-panel{margin-top:8px;padding:20px;border-radius:22px;background:linear-gradient(180deg,#fff,#fff8f1);border:1px solid rgba(114,14,7,.12);text-align:left}
.payment-panel.is-bizum{background:linear-gradient(180deg,#fbfff9,#f2fbef);border-color:rgba(60,112,57,.18)}
.payment-panel p{margin:0 0 8px}
.payment-panel:last-child p:last-child{margin-bottom:0}
.payment-panel-title{display:flex;align-items:center;gap:10px;margin-bottom:8px;color:#45050C}
.payment-panel-icon{display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:14px;background:#fff;box-shadow:inset 0 0 0 1px rgba(114,14,7,.08)}
.payment-panel-icon svg{width:22px;height:22px;stroke:currentColor;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.payment-note{color:rgba(69,5,12,.75);line-height:1.5}
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
          <a href="perfil.php" data-i18n="nav.myProfile">Mi perfil</a>
          <a href="politica_cookies.php" class="open-cookie-preferences" data-i18n="nav.customizeCookies">Personalizar cookies</a>
          <a href="logout.php" data-i18n="nav.logout">Cerrar Sesion</a>
      </div>
    </div>

    <a href="usuario.php" class="landing-logo">
      <span class="landing-logo-text">Zyma</span>
    </a>

    <div class="quick-menu-section">
      <button class="quick-menu-btn" id="quickMenuBtn" aria-label="Menu rapido"></button>
      <div class="dropdown quick-dropdown" id="quickDropdown">
        <a href="usuario.php" data-i18n="nav.home">Inicio</a>
        <a href="carta.php" data-i18n="nav.viewMenu">Ver carta</a>
        <a href="valoraciones.php" data-i18n="nav.reviews">Valoraciones</a>
        <a href="tickets.php" data-i18n="nav.tickets">Tickets</a>
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
  <div class="cart-container">
    <div class="cart-header">
      <h1 data-i18n="cart.title">Tu Carrito</h1>
      <p data-i18n="cart.subtitle">Revisa tus productos antes de finalizar el pedido</p>
    </div>

    <?php if (empty($cartItems)): ?>
      <div class="empty-cart">
        <p class="empty-state" data-i18n="cart.empty">Tu carrito esta vacio.</p>
        <a href="carta.php" class="btn-seguir-comprando" data-i18n="cart.continueShopping">Seguir comprando</a>
      </div>
    <?php else: ?>
      <?php foreach ($cartItems as $item): ?>
      <div class="cart-item-row">
        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-img">

        <div class="cart-item-info">
          <div class="cart-item-name" data-i18n="product.<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></div>
          <div class="cart-item-meta" id="desc-<?= (int) $item['id'] ?>">
            EUR <?= number_format((float) $item['price'], 2, ',', '.') ?> x <?= (int) $item['quantity'] ?>
          </div>
          <div class="cart-item-subtotal" id="subtotal-<?= (int) $item['id'] ?>">
            EUR <?= number_format((float) $item['subtotal'], 2, ',', '.') ?>
          </div>
        </div>

        <div class="quantity-controls">
          <button class="quantity-btn" onclick="changeQuantity(<?= (int) $item['id'] ?>, -1)">-</button>
          <span class="quantity-value" id="qty-<?= (int) $item['id'] ?>"><?= (int) $item['quantity'] ?></span>
          <button class="quantity-btn" onclick="changeQuantity(<?= (int) $item['id'] ?>, 1)">+</button>
        </div>
      </div>
      <?php endforeach; ?>

      <div class="center mt-3">
        <div class="cart-total-line" id="total-amount">
          Total: EUR <?= number_format($total, 2, ',', '.') ?>
        </div>

        <div class="btn-row center">
          <form method="POST">
            <?php if (!empty($error)): ?>
              <p class="empty-state"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <div style="text-align:left;margin-bottom:12px;">
              <strong data-i18n="cart.paymentMethod">Metodo de pago online</strong>
              <div class="payment-methods">
                <label class="payment-option">
                  <input type="radio" name="metodo_pago" value="tarjeta" checked>
                  <span class="payment-option-card">
                    <strong data-i18n="cart.cardOption">Tarjeta</strong>
                    <span>Stripe Checkout</span>
                  </span>
                </label>
                <label class="payment-option">
                  <input type="radio" name="metodo_pago" value="bizum">
                  <span class="payment-option-card is-bizum">
                    <strong data-i18n="cart.bizumOption">Bizum</strong>
                    <span>Pago guiado</span>
                  </span>
                </label>
              </div>
            </div>

            <div id="card-fields" class="payment-panel">
              <div class="payment-panel-title">
                <span class="payment-panel-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24">
                    <rect x="3" y="6" width="18" height="12" rx="2"></rect>
                    <path d="M3 10h18"></path>
                  </svg>
                </span>
                <strong>Pago con tarjeta</strong>
              </div>
              <p class="payment-note">Seras redirigido a la pagina segura de Stripe para completar el pago.</p>
            </div>

            <div id="bizum-fields" class="payment-panel is-bizum" style="display:none;">
              <div class="payment-panel-title">
                <span class="payment-panel-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24">
                    <path d="M12 3v18"></path>
                    <path d="M8 7.5c0-1.7 1.8-3 4-3s4 1.3 4 3-1.8 3-4 3-4 1.3-4 3 1.8 3 4 3 4-1.3 4-3"></path>
                  </svg>
                </span>
                <strong>Pago con Bizum</strong>
              </div>
              <p class="payment-note">Te mostraremos las instrucciones en pantalla para enviar el importe exacto y confirmar tu pedido.</p>
              <label for="telefono_bizum" style="display:block;margin-top:12px;">Tu telefono de Bizum (opcional)</label>
              <input type="text" id="telefono_bizum" name="telefono_bizum" placeholder="+34XXXXXXXXX" style="width:100%;margin-top:6px;">
            </div>

            <button type="submit" name="pagar_online" class="btn-realizar-pedido btn-block" data-i18n="cart.payOnline">
              Pagar online
            </button>
          </form>
          <a href="carta.php" class="btn-seguir-comprando" data-i18n="cart.continueShopping">Seguir comprando</a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

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

function changeQuantity(productId, delta) {
  const qtyElement = document.getElementById('qty-' + productId);
  const descElement = document.getElementById('desc-' + productId);
  const subtotalElement = document.getElementById('subtotal-' + productId);
  const totalElement = document.getElementById('total-amount');

  let currentQty = parseInt(qtyElement.textContent, 10);

  if (delta === -1 && currentQty === 1) {
    window.location.href = 'eliminar_producto.php?id=' + productId;
    return;
  }

  if (currentQty + delta < 1) return;

  currentQty += delta;
  qtyElement.textContent = currentQty;

  const prices = [0, 6.00, 3.50, 7.50, 5.99, 6.50, 2.00, 1.50];

  descElement.innerHTML = 'EUR ' + prices[productId].toFixed(2).replace('.', ',') + ' x ' + currentQty;
  const subtotal = prices[productId] * currentQty;
  subtotalElement.innerHTML = 'EUR ' + subtotal.toFixed(2).replace('.', ',');

  let total = 0;
  <?php foreach ($cartItems as $item): ?>
  if (<?= (int) $item['id'] ?> === productId) {
    total += <?= (float) $item['price'] ?> * currentQty;
  } else {
    total += <?= (float) $item['price'] ?> * <?= (int) $item['quantity'] ?>;
  }
  <?php endforeach; ?>

  totalElement.innerHTML = 'Total: EUR ' + total.toFixed(2).replace('.', ',');
}

const methodRadios = document.querySelectorAll('input[name="metodo_pago"]');
const cardFields = document.getElementById('card-fields');
const bizumFields = document.getElementById('bizum-fields');

function togglePaymentFields() {
  const selected = document.querySelector('input[name="metodo_pago"]:checked');
  if (!selected || !cardFields || !bizumFields) return;

  const isCard = selected.value === 'tarjeta';
  cardFields.style.display = isCard ? 'block' : 'none';
  bizumFields.style.display = isCard ? 'none' : 'block';
}

methodRadios.forEach((radio) => {
  radio.addEventListener('change', togglePaymentFields);
});
togglePaymentFields();
</script>
<script src="assets/mobile-header.js?v=20260211-6"></script>
<script src="assets/lang.js?v=1"></script>
<footer>
  <p>&copy; 2025 Zyma. Todos los derechos reservados.</p>
  <p class="footer-legal-links">
    <a href="politica_cookies.php" data-i18n="footer.cookiePolicy">Politica de Cookies</a>
    <span>|</span>
    <a href="politica_privacidad.php" data-i18n="footer.privacy">Politica de Privacidad</a>
    <span>|</span>
    <a href="aviso_legal.php" data-i18n="footer.legal">Aviso Legal</a>
  </p></footer>
</body>
</html>

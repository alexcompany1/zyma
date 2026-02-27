<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

/**
 * carrito.php
 * Muestra el carrito y procesa pago por tarjeta o Bizum.
 */

require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$products = [
    ['id' => 1, 'name' => 'Nachos con Queso', 'price' => 6.00, 'image' => 'assets/nachos.png'],
    ['id' => 2, 'name' => 'Patatas Fritas', 'price' => 3.50, 'image' => 'assets/fries.png'],
    ['id' => 3, 'name' => 'Hotdog BBQ', 'price' => 7.50, 'image' => 'assets/bbq_hotdog.png'],
    ['id' => 4, 'name' => 'Hotdog Clasico', 'price' => 5.99, 'image' => 'assets/hotdog.png'],
    ['id' => 5, 'name' => 'Hotdog Vegano', 'price' => 6.50, 'image' => 'assets/vegan-hotdog.png'],
    ['id' => 6, 'name' => 'Refresco Cola', 'price' => 2.00, 'image' => 'assets/soda.png'],
    ['id' => 7, 'name' => 'Agua Mineral', 'price' => 1.50, 'image' => 'assets/water.png']
];

$cartItems = [];
$total = 0;

foreach ($_SESSION['cart'] ?? [] as $id => $qty) {
    foreach ($products as $product) {
        if ($product['id'] == $id) {
            $product['quantity'] = (int) $qty;
            $product['subtotal'] = $product['price'] * (int) $qty;
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
            $metodo_pago = $_POST['metodo_pago'] ?? '';
            if (!in_array($metodo_pago, ['tarjeta', 'bizum'], true)) {
                throw new Exception('Selecciona un metodo de pago valido.');
            }

            if ($metodo_pago === 'tarjeta') {
                $numero_tarjeta = preg_replace('/\D+/', '', $_POST['numero_tarjeta'] ?? '');
                $caducidad = trim($_POST['caducidad'] ?? '');
                $cvv = preg_replace('/\D+/', '', $_POST['cvv'] ?? '');

                if (strlen($numero_tarjeta) < 13 || strlen($numero_tarjeta) > 19) {
                    throw new Exception('Numero de tarjeta invalido.');
                }
                if (!preg_match('/^\d{2}\/\d{2}$/', $caducidad)) {
                    throw new Exception('Fecha de caducidad invalida.');
                }
                if (strlen($cvv) < 3 || strlen($cvv) > 4) {
                    throw new Exception('CVV invalido.');
                }
            } else {
                $telefono_bizum = preg_replace('/\s+/', '', $_POST['telefono_bizum'] ?? '');
                if (!preg_match('/^(\+34)?[6789]\d{8}$/', $telefono_bizum)) {
                    throw new Exception('Telefono Bizum invalido.');
                }
            }

            // calcular siguiente id_pedido manualmente
            $stmtNext = $pdo->query("SELECT COALESCE(MAX(id_pedido),0) FROM pedidos");
            $nextId = (int)$stmtNext->fetchColumn() + 1;

            $stmt = $pdo->prepare("\n                INSERT INTO pedidos (id_pedido, id_mesa, id_usuario, fecha_hora, estado, total, metodo_pago, notas_cliente)\n                VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)\n            ");

            $stmt->execute([
                $nextId,
                0,
                $_SESSION['user_id'],
                'pendiente',
                $total,
                $metodo_pago,
                ''
            ]);

            $pedidoId = $nextId;

            $display_name = trim($_SESSION['nombre'] ?? '');
            if ($display_name === '') {
                $display_name = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? '');
            }

            $stmtNot = $pdo->prepare("\n                INSERT INTO notificaciones (id_usuario, mensaje, leida, fecha)\n                VALUES (?, ?, 0, NOW())\n            ");
            $mensaje = 'Nuevo pedido #' . $pedidoId . ' de ' . $display_name . ' por ' . number_format($total, 2, ',', '.') . ' EUR';
            $stmtNot->execute([$_SESSION['user_id'], $mensaje]);

            unset($_SESSION['cart']);
            // Redirigir directamente al ticket recién generado
+            header('Location: ticket.php?id=' . $pedidoId);
+            exit;
        } catch (Exception $e) {
            $error = 'Error al procesar el pedido: ' . $e->getMessage();
        }
    }
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
<title>Zyma - Tu Carrito</title>
<link rel="stylesheet" href="styles.css?v=20260211-5">
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
        <a href="valoraciones.php">Valoraciones</a>
        <a href="tickets.php">Tickets</a>
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
      <h1>Tu Carrito</h1>
      <p>Revisa tus productos antes de finalizar el pedido</p>
    </div>

    <?php if (empty($cartItems)): ?>
      <div class="empty-cart">
        <p class="empty-state">Tu carrito esta vacio.</p>
        <a href="carta.php" class="btn-seguir-comprando">Seguir comprando</a>
      </div>
    <?php else: ?>
      <?php foreach ($cartItems as $item): ?>
      <div class="cart-item-row">
        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-img">

        <div class="cart-item-info">
          <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
          <div class="cart-item-meta" id="desc-<?= $item['id'] ?>">
            EUR <?= number_format($item['price'], 2, ',', '.') ?> x <?= $item['quantity'] ?>
          </div>
          <div class="cart-item-subtotal" id="subtotal-<?= $item['id'] ?>">
            EUR <?= number_format($item['subtotal'], 2, ',', '.') ?>
          </div>
        </div>

        <div class="quantity-controls">
          <button class="quantity-btn" onclick="changeQuantity(<?= $item['id'] ?>, -1)">-</button>
          <span class="quantity-value" id="qty-<?= $item['id'] ?>"><?= $item['quantity'] ?></span>
          <button class="quantity-btn" onclick="changeQuantity(<?= $item['id'] ?>, 1)">+</button>
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
              <strong>Metodo de pago online</strong>
              <div style="margin-top:8px;">
                <label style="margin-right:16px;">
                  <input type="radio" name="metodo_pago" value="tarjeta" checked> Tarjeta
                </label>
                <label>
                  <input type="radio" name="metodo_pago" value="bizum"> Bizum
                </label>
              </div>
            </div>

            <div id="card-fields" style="text-align:left;margin-bottom:12px;">
              <input type="text" name="numero_tarjeta" placeholder="Numero de tarjeta" maxlength="19" style="width:100%;margin-bottom:8px;">
              <div style="display:flex;gap:8px;">
                <input type="text" name="caducidad" placeholder="MM/AA" maxlength="5" style="width:50%;">
                <input type="text" name="cvv" placeholder="CVV" maxlength="4" style="width:50%;">
              </div>
            </div>

            <div id="bizum-fields" style="display:none;text-align:left;margin-bottom:12px;">
              <input type="text" name="telefono_bizum" placeholder="Telefono Bizum (+34XXXXXXXXX)" style="width:100%;">
            </div>

            <button type="submit" name="pagar_online" class="btn-realizar-pedido btn-block">
              Pagar online
            </button>
          </form>
          <a href="carta.php" class="btn-seguir-comprando">Seguir comprando</a>
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
  if (<?= $item['id'] ?> === productId) {
    total += <?= $item['price'] ?> * currentQty;
  } else {
    total += <?= $item['price'] ?> * <?= $item['quantity'] ?>;
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
</body>
</html>
<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

require_once 'config.php';
session_start();

$guestMode = false;
if (!isset($_SESSION['user_id'])) {
    if (isset($_GET['guest']) && $_GET['guest'] === '1') {
        $_SESSION['guest_mode'] = true;
    }

    if (!empty($_SESSION['guest_mode'])) {
        $guestMode = true;
    } else {
        header('Location: login.php');
        exit;
    }
} else {
    unset($_SESSION['guest_mode']);
}

$selectedProductId = isset($_GET['producto']) ? (int) $_GET['producto'] : 0;

if (!$guestMode && !isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Cargar extras disponibles
$allExtras = [];
$productExtrasMap = [];
if (!$guestMode) {
    try {
        $stmt = $pdo->query("SELECT id, nombre, precio_adicional FROM product_extras WHERE activo = 1 ORDER BY nombre");
        $allExtras = $stmt->fetchAll();
        $stmt = $pdo->query("SELECT id_producto, id_extra FROM producto_extras ORDER BY id_producto");
        foreach ($stmt->fetchAll() as $row) {
            $pid = (int)$row['id_producto'];
            if (!isset($productExtrasMap[$pid])) $productExtrasMap[$pid] = [];
            $productExtrasMap[$pid][] = (int)$row['id_extra'];
        }
    } catch (Exception $e) {
        $allExtras = [];
        $productExtrasMap = [];
    }
}

$show_cookie_popup = false;
$cookie_preferences = [];
if (!$guestMode) {
  $show_cookie_popup = !empty($_SESSION['show_cookie_popup']);
  $cookie_preferences = $_SESSION['cookie_preferences'] ?? [];
  unset($_SESSION['show_cookie_popup']);
}

$display_name = '';
$cart_count = 0;
$unread_count = 0;
if (!$guestMode) {
    $display_name = trim($_SESSION['nombre'] ?? '');
    if ($display_name === '') {
        $display_name = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? '');
    }
    $cart_count = zymaCartTotalItems();

    try {
        $stmtNotif = $pdo->prepare("
            SELECT COUNT(*)
            FROM notificaciones
            WHERE id_usuario = :id_usuario AND leida = 0
        ");
        $stmtNotif->execute([':id_usuario' => $_SESSION['user_id']]);
        $unread_count = (int)$stmtNotif->fetchColumn();
    } catch (Exception $e) {
        $unread_count = 0;
    }
}

try {
    $stmt = $pdo->query("
        SELECT 
            p.id, 
            p.nombre, 
            p.precio, 
            p.imagen,
            ROUND(AVG(v.puntuacion), 1) as promedio_puntuacion,
            COUNT(v.id) as total_valoraciones
        FROM productos p
        LEFT JOIN valoraciones v ON p.id = v.id_producto
        GROUP BY p.id
        ORDER BY p.nombre
    ");
    $products_db = $stmt->fetchAll();

    $products = [];
    foreach ($products_db as $product) {
        $products[] = [
            'id' => $product['id'],
            'name' => $product['nombre'],
            'description' => '',
            'price' => (float)$product['precio'],
            'image' => $product['imagen'],
            'allergens' => [],
            'promedio' => (float)($product['promedio_puntuacion'] ?? 0),
            'total_valoraciones' => (int)($product['total_valoraciones'] ?? 0)
        ];
    }
} catch (Exception $e) {
    $products = [
        ['id' => 1, 'name' => 'Nachos con Queso', 'description' => 'Tortillas crujientes cubiertas con queso fundido y jalapenos.', 'price' => 6.00, 'image' => 'assets/nachos.png', 'allergens' => ['gluten', 'lacteos']],
        ['id' => 2, 'name' => 'Patatas Fritas', 'description' => 'Crujientes y doradas, servidas con sal marina.', 'price' => 3.50, 'image' => 'assets/fries.png', 'allergens' => []],
        ['id' => 3, 'name' => 'Hotdog BBQ', 'description' => 'Salchicha ahumada, salsa BBQ casera, cebolla caramelizada y queso derretido.', 'price' => 7.50, 'image' => 'assets/bbq_hotdog.png', 'allergens' => ['gluten', 'lacteos', 'soja']],
        ['id' => 4, 'name' => 'Hotdog Clasico', 'description' => 'Pan artesanal, salchicha premium, mostaza y cebolla crujiente.', 'price' => 5.99, 'image' => 'assets/hotdog.png', 'allergens' => ['gluten', 'soja']],
        ['id' => 5, 'name' => 'Hotdog Vegano', 'description' => 'Salchicha vegetal, mayonesa vegana, pepinillos y mostaza.', 'price' => 6.50, 'image' => 'assets/vegan-hotdog.png', 'allergens' => ['gluten', 'soja']],
        ['id' => 6, 'name' => 'Refresco Cola', 'description' => 'Bebida refrescante y burbujeante.', 'price' => 2.00, 'image' => 'assets/soda.png', 'allergens' => []],
        ['id' => 7, 'name' => 'Agua Mineral', 'description' => 'Agua pura y natural, sin gas.', 'price' => 1.00, 'image' => 'assets/water.png', 'allergens' => []]
    ];
}

$starProductId = 0;
$starProductName = '';
if (!empty($products)) {
    $rankedProducts = $products;
    usort($rankedProducts, static function ($a, $b) {
        $isHotdogA = stripos((string)($a['name'] ?? ''), 'hotdog') !== false ? 1 : 0;
        $isHotdogB = stripos((string)($b['name'] ?? ''), 'hotdog') !== false ? 1 : 0;
        $scoreA = (float)($a['promedio'] ?? 0);
        $scoreB = (float)($b['promedio'] ?? 0);
        $reviewsA = (int)($a['total_valoraciones'] ?? 0);
        $reviewsB = (int)($b['total_valoraciones'] ?? 0);

        if ($isHotdogA !== $isHotdogB) {
            return $isHotdogB <=> $isHotdogA;
        }

        if ($scoreA === $scoreB) {
            if ($reviewsA === $reviewsB) {
                return strcmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
            }
            return $reviewsB <=> $reviewsA;
        }

        return $scoreB <=> $scoreA;
    });

    $starProductId = (int)($rankedProducts[0]['id'] ?? 0);
    $starProductName = (string)($rankedProducts[0]['name'] ?? '');
}

if ($selectedProductId > 0) {
    $products = array_values(array_filter($products, static function ($product) use ($selectedProductId) {
        return (int)($product['id'] ?? 0) === $selectedProductId;
    }));
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zyma - Carta</title>
<link rel="icon" type="image/png" href="assets/favicon.png">
<link rel="shortcut icon" type="image/png" href="assets/favicon.png">
<link rel="stylesheet" href="styles.css?v=20260513-1">
</head>
<body class="page-enter">
<header class="landing-header">
  <div class="landing-bar">
    <?php if ($guestMode): ?>
      <a href="index.php" class="landing-logo">
        <img src="assets/zyma.jpg" alt="Zyma" class="landing-logo-img">
        <span class="landing-logo-text">Zyma</span>
      </a>
      <div class="landing-actions">
        <a href="login.php" class="landing-link">Entrar</a>
        <a href="registro.php" class="landing-cta">Crear cuenta</a>
      </div>
    <?php else: ?>
      <div class="profile-section">
        <button class="profile-btn" id="profileBtn" aria-label="Perfil">
          <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="white">
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c1.52 0 5.1 1.34 5.1 5v1H6.9v-1c0-3.66 3.58-5 5.1-5z"/>
          </svg>
        </button>
        <span class="user-name"><?= htmlspecialchars($display_name) ?></span>
        <div class="dropdown" id="dropdownMenu">
          <a href="perfil.php">Mi perfil</a>
          <a href="politica_cookies.php" class="open-cookie-preferences">Personalizar cookies</a>
          <a href="logout.php">Cerrar Sesión</a>
        </div>
      </div>

      <a href="usuario.php" class="landing-logo">
        <img src="assets/zyma.jpg" alt="Zyma" class="landing-logo-img">
        <span class="landing-logo-text">Zyma</span>
      </a>

          <div class="quick-menu-section">
        <button class="quick-menu-btn" id="quickMenuBtn" aria-label="Menú rápido">
          <svg class="quick-menu-icon" viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
            <line x1="5" y1="7" x2="19" y2="7" />
            <line x1="5" y1="12" x2="19" y2="12" />
            <line x1="5" y1="17" x2="19" y2="17" />
          </svg>
        </button>
        <div class="dropdown quick-dropdown" id="quickDropdown">
          <a href="usuario.php">Inicio</a>
          <a href="carta.php">Ver carta</a>
          <a href="valoraciones.php">Valoraciones</a>
          <a href="tickets.php">Tickets</a>
        </div>
      </div>
    <div class="cart-section">
        <a href="notificaciones.php" class="cart-btn" aria-label="Notificaciones">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="white">
            <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6V11c0-3.07-1.64-5.64-4.5-6.32V4a1.5 1.5 0 1 0-3 0v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
          </svg>
          <?php if ($unread_count > 0): ?>
            <span class="cart-count"><?= $unread_count ?></span>
          <?php endif; ?>
        </a>
        <a href="carrito.php" class="cart-btn" aria-label="Carrito">
          <img src="assets/cart-icon.png" alt="Carrito">
          <span class="cart-count"><?= $cart_count ?></span>
        </a>
      </div>
    <?php endif; ?>
  </div>
</header>

<div class="container">
  <div class="hero reveal">
    <h1 class="hero-title">Carta de Zyma</h1>
    <p class="hero-sub"><?= $selectedProductId > 0 ? 'Vista destacada de nuestro producto estrella.' : 'Disfruta de nuestros deliciosos platos artesanales.' ?></p>
    <?php if ($starProductId > 0): ?>
      <p class="menu-star-callout">Producto estrella: <a href="#producto-<?= $starProductId ?>"><?= htmlspecialchars($starProductName) ?></a></p>
    <?php endif; ?>
    <?php if ($selectedProductId > 0): ?>
      <p class="menu-star-callout"><a href="carta.php">Volver a toda la carta</a></p>
    <?php endif; ?>
    <?php if ($guestMode): ?>
      <p class="muted mt-1">Modo invitado: puedes ver la carta, para pedir necesitas iniciar Sesión.</p>
    <?php endif; ?>
  </div>

  <div class="grid grid-products reveal-stagger" data-stagger-delay="100">
    <?php foreach ($products as $product): ?>
    <div class="card-product hover-lift <?= ((int)$product['id'] === $starProductId) ? 'card-product-star' : '' ?>" id="producto-<?= (int)$product['id'] ?>">
      <img src="<?= htmlspecialchars($product['image']) ?>"
           alt="<?= htmlspecialchars($product['name']) ?>"
           onerror="this.src='assets/default-product.png';">
      <div class="card-content">
        <?php if ((int)$product['id'] === $starProductId): ?>
          <span class="product-star-badge">Producto estrella</span>
        <?php endif; ?>
        <h2 class="card-title"><?= htmlspecialchars($product['name']) ?></h2>
        
        <!-- Estrellas de valoración -->
        <div class="product-rating">
          <?php 
          $promedio = $product['promedio'] ?? 0;
          $total = $product['total_valoraciones'] ?? 0;
          for ($i = 1; $i <= 5; $i++): 
            $starClass = $i <= round($promedio) ? 'star-filled' : 'star-empty';
          ?>
            <img src="assets/<?= $i <= round($promedio) ? 'estrellaSelecionada.png' : 'estrellaNegra.png' ?>" 
                 alt="Estrella" class="rating-star <?= $starClass ?>" style="width:11px;height:11px;">
          <?php endfor; ?>
          <span class="rating-text"><?= number_format($promedio, 1) ?> (<?= $total ?>)</span>
        </div>
        
        <p class="card-description"><?= htmlspecialchars($product['description']) ?></p>
        <p class="card-price">EUR <?= number_format($product['price'], 2, ',', '.') ?></p>

        <div class="allergen-tags">
          <?php if (in_array('gluten', $product['allergens'], true)): ?>
            <span class="allergen-tag tag-gluten">Gluten</span>
          <?php endif; ?>
          <?php if (in_array('lacteos', $product['allergens'], true)): ?>
            <span class="allergen-tag tag-lacteos">Lacteos</span>
          <?php endif; ?>
          <?php if (in_array('soja', $product['allergens'], true)): ?>
            <span class="allergen-tag tag-soja">Soja</span>
          <?php endif; ?>
        </div>

        <?php if ($guestMode): ?>
          <a href="login.php" class="btn-add-cart">Inicia Sesión para pedir</a>
          <a href="login.php" class="btn-valorar">Valorar producto</a>
        <?php else: ?>
          <button type="button" class="btn-add-cart" onclick="openCustomizer(<?= (int)$product['id'] ?>)">Personalizar</button>
          <a href="valoraciones.php?producto=<?= (int)$product['id'] ?>" class="btn-valorar">Valorar producto</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php if ($guestMode): ?>
<div class="popup-overlay" id="customizerModal" hidden>
  <div class="popup-card" style="max-width:480px;text-align:left;padding:1.8rem;position:relative;">
    <button type="button" onclick="closeCustomizer()" style="position:absolute;top:10px;right:14px;background:none;border:none;font-size:1.6rem;cursor:pointer;color:#888;line-height:1;">&times;</button>
    <div id="customizerContent"></div>
  </div>
</div>
<?php else: ?>
<div class="popup-overlay" id="customizerModal" hidden>
  <div class="popup-card" style="max-width:480px;text-align:left;padding:1.8rem;position:relative;">
    <button type="button" onclick="closeCustomizer()" style="position:absolute;top:10px;right:14px;background:none;border:none;font-size:1.6rem;cursor:pointer;color:#888;line-height:1;">&times;</button>
    <form method="POST" action="procesar_pedido_personalizado.php" id="customizerForm">
      <input type="hidden" name="product_id" id="customProductId" value="0">
      <div id="customizerContent"></div>
      <div style="display:flex;gap:10px;margin-top:16px;">
        <button type="submit" class="btn-add-cart" style="flex:1;">Anadir al carrito</button>
        <button type="button" class="btn-valorar" onclick="closeCustomizer()" style="flex:1;text-align:center;">Cancelar</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if (!$guestMode && isset($_SESSION['toast_message'])): ?>
<div class="toast-notification" id="toastNotification">
  <div class="toast-icon"><?= $_SESSION['toast_message']['icon'] ?></div>
  <span><?= $_SESSION['toast_message']['text'] ?></span>
</div>
<?php unset($_SESSION['toast_message']); endif; ?>

<?php if (!$guestMode): ?>
  <?php include 'cookie_popup.php'; ?>
<?php endif; ?>

<script>
<?php if (!$guestMode): ?>
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

const productExtras = <?= json_encode($productExtrasMap, JSON_UNESCAPED_UNICODE) ?>;
const extrasData = <?= json_encode($allExtras, JSON_UNESCAPED_UNICODE) ?>;
const productsData = <?= json_encode(array_map(function($p) {
    return ['id' => $p['id'], 'name' => $p['name'], 'price' => $p['price'], 'image' => $p['image']];
}, $products), JSON_UNESCAPED_UNICODE) ?>;

const modal = document.getElementById('customizerModal');
const content = document.getElementById('customizerContent');
const productIdInput = document.getElementById('customProductId');

let currentQty = 1;
let currentProductId = 0;

function openCustomizer(productId) {
  currentProductId = productId;
  currentQty = 1;
  productIdInput.value = productId;

  const prod = productsData.find(p => p.id === productId);
  if (!prod) return;

  const extraIds = productExtras[productId] || [];
  const available = extrasData.filter(e => extraIds.indexOf(e.id) !== -1);

  let html = '<div style="display:flex;gap:14px;align-items:center;margin-bottom:14px;">';
  html += '<img src="' + prod.image + '" alt="' + prod.name + '" style="width:70px;height:70px;object-fit:cover;border-radius:10px;" onerror="this.src=\'assets/default-product.png\';">';
  html += '<div><h3 style="margin:0;color:#45050C;">' + prod.name + '</h3>';
  html += '<p style="margin:4px 0 0;color:#888;" id="customBasePrice">EUR ' + prod.price.toFixed(2).replace('.', ',') + '</p></div></div>';

  html += '<div style="margin-bottom:14px;"><label style="font-weight:600;color:#45050C;">Cantidad</label>';
  html += '<div style="display:flex;align-items:center;gap:10px;margin-top:6px;">';
  html += '<button type="button" class="quantity-btn" onclick="changeQty(-1)">-</button>';
  html += '<span id="customQty" class="quantity-value">1</span>';
  html += '<button type="button" class="quantity-btn" onclick="changeQty(1)">+</button>';
  html += '</div></div>';

  if (available.length > 0) {
    html += '<div style="margin-bottom:10px;"><label style="font-weight:600;color:#45050C;">Extras</label>';
    html += '<div style="margin-top:6px;display:flex;flex-direction:column;gap:6px;">';
    available.forEach(function(ex) {
      var priceStr = parseFloat(ex.precio_adicional) > 0 ? ' (+' + parseFloat(ex.precio_adicional).toFixed(2).replace('.', ',') + ' EUR)' : '';
      html += '<label style="display:flex;align-items:center;gap:8px;padding:6px 10px;border:1px solid #e0d6ce;border-radius:8px;cursor:pointer;">';
      html += '<input type="checkbox" name="extras[]" value="' + ex.id + '" onchange="updateTotal()">';
      html += '<span style="flex:1;">' + ex.nombre + '</span>';
      html += '<span style="color:#888;font-size:.9rem;">' + priceStr + '</span>';
      html += '</label>';
    });
    html += '</div></div>';
  }

  html += '<div style="border-top:1px solid #eee;padding-top:12px;margin-top:6px;text-align:right;">';
  html += '<span style="font-size:.9rem;color:#888;">Total: </span>';
  html += '<span id="customTotal" style="font-size:1.3rem;font-weight:800;color:#45050C;">EUR ' + prod.price.toFixed(2).replace('.', ',') + '</span>';
  html += '</div>';

  content.innerHTML = html;
  modal.hidden = false;
}

function closeCustomizer() {
  modal.hidden = true;
}

function changeQty(delta) {
  var el = document.getElementById('customQty');
  var newQty = parseInt(el.textContent) + delta;
  if (newQty < 1) newQty = 1;
  el.textContent = newQty;
  updateTotal();
}

function updateTotal() {
  var prod = productsData.find(p => p.id === currentProductId);
  if (!prod) return;
  var qty = parseInt(document.getElementById('customQty').textContent);
  var base = prod.price;
  var extrasTotal = 0;
  document.querySelectorAll('#customizerForm input[name=\"extras[]\"]:checked').forEach(function(cb) {
    var ex = extrasData.find(function(e) { return e.id == cb.value; });
    if (ex) extrasTotal += parseFloat(ex.precio_adicional);
  });
  var total = (base + extrasTotal) * qty;
  document.getElementById('customTotal').textContent = 'EUR ' + total.toFixed(2).replace('.', ',');
}

modal.addEventListener('click', function(e) {
  if (e.target === modal) closeCustomizer();
});
<?php endif; ?>

const toast = document.getElementById('toastNotification');
if (toast) {
  setTimeout(() => {
    toast.classList.add('show');
  }, 100);

  setTimeout(() => {
    toast.classList.remove('show');
  }, 3000);
}
</script>
<script src="assets/mobile-header.js?v=20260513-1"></script>
<script src="assets/animations.js?v=20260513-1" defer></script>
<footer>
  <p>&copy; 2025 Zyma. Todos los derechos reservados.</p>
  <p class="footer-legal-links">
    <a href="politica_cookies.php">Política de Cookies</a>
    <span>|</span>
    <a href="politica_privacidad.php">Política de Privacidad</a>
    <span>|</span>
    <a href="aviso_legal.php">Aviso Legal</a>
  </p></footer>

<?php require_once 'language_selector.php'; ?>
</body>
</html>


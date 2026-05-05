<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

require_once 'config.php';
session_start();
require_once 'auth.php';

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
    if (zymaCurrentRole() !== 'client') {
        zymaRedirectToHomeForCurrentRole();
    }
}

$selectedProductId = isset($_GET['producto']) ? (int) $_GET['producto'] : 0;

if (!$guestMode && !isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
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
    $cart_count = count($_SESSION['cart'] ?? []);

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

    // Obtener extras y alérgenos para todos los productos
    $extras_stmt = $pdo->query("
        SELECT pe.id_producto, e.id as extra_id, e.nombre as extra_nombre, e.precio_adicional
        FROM producto_extras pe
        JOIN product_extras e ON pe.id_extra = e.id
        WHERE e.activo = 1
    ");
    $extras_db = $extras_stmt->fetchAll();

    $allergens_stmt = $pdo->query("
        SELECT pa.id_producto, a.id as allergeno_id, a.nombre as allergeno_nombre, a.icono
        FROM producto_allergens pa
        JOIN product_allergens a ON pa.id_allergeno = a.id
    ");
    $allergens_db = $allergens_stmt->fetchAll();

    $products_extras = [];
    foreach ($extras_db as $extra) {
        $products_extras[$extra['id_producto']][] = [
            'id' => (int)$extra['extra_id'],
            'name' => $extra['extra_nombre'],
            'price' => (float)$extra['precio_adicional']
        ];
    }

    $products_allergens = [];
    foreach ($allergens_db as $allergen) {
        $products_allergens[$allergen['id_producto']][] = [
            'id' => (int)$allergen['allergeno_id'],
            'name' => $allergen['allergeno_nombre'],
            'icon' => $allergen['icono']
        ];
    }

    $products = [];
    foreach ($products_db as $product) {
        $pid = (int)$product['id'];
        $products[] = [
            'id' => $pid,
            'name' => $product['nombre'],
            'description' => '',
            'price' => (float)$product['precio'],
            'image' => $product['imagen'],
            'extras' => $products_extras[$pid] ?? [],
            'allergens' => $products_allergens[$pid] ?? [],
            'promedio' => (float)($product['promedio_puntuacion'] ?? 0),
            'total_valoraciones' => (int)($product['total_valoraciones'] ?? 0)
        ];
    }
} catch (Exception $e) {
    $products = [
        ['id' => 1, 'name' => 'Nachos con Queso', 'description' => '', 'price' => 6.00, 'image' => 'assets/nachos.png', 'extras' => [], 'allergens' => [['name' => 'gluten'], ['name' => 'lacteos']], 'promedio' => 0, 'total_valoraciones' => 0],
        ['id' => 2, 'name' => 'Patatas Fritas', 'description' => '', 'price' => 3.50, 'image' => 'assets/fries.png', 'extras' => [], 'allergens' => [], 'promedio' => 0, 'total_valoraciones' => 0],
        ['id' => 3, 'name' => 'Hotdog BBQ', 'description' => '', 'price' => 7.50, 'image' => 'assets/bbq_hotdog.png', 'extras' => [['id' => 1, 'name' => 'Queso extra', 'price' => 1.00], ['id' => 2, 'name' => 'Bacon', 'price' => 1.50]], 'allergens' => [['name' => 'gluten'], ['name' => 'soja']], 'promedio' => 0, 'total_valoraciones' => 0],
        ['id' => 4, 'name' => 'Hotdog Clásico', 'description' => '', 'price' => 5.99, 'image' => 'assets/hotdog.png', 'extras' => [['id' => 1, 'name' => 'Queso extra', 'price' => 1.00]], 'allergens' => [['name' => 'gluten'], ['name' => 'soja']], 'promedio' => 0, 'total_valoraciones' => 0],
        ['id' => 5, 'name' => 'Hotdog Vegano', 'description' => '', 'price' => 6.50, 'image' => 'assets/vegan-hotdog.png', 'extras' => [], 'allergens' => [['name' => 'gluten'], ['name' => 'soja']], 'promedio' => 0, 'total_valoraciones' => 0],
        ['id' => 6, 'name' => 'Refresco Cola', 'description' => '', 'price' => 2.00, 'image' => 'assets/soda.png', 'extras' => [], 'allergens' => [], 'promedio' => 0, 'total_valoraciones' => 0],
        ['id' => 7, 'name' => 'Agua Mineral', 'description' => '', 'price' => 1.00, 'image' => 'assets/water.png', 'extras' => [], 'allergens' => [], 'promedio' => 0, 'total_valoraciones' => 0]
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

if (!$guestMode && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $pid = (int)($_POST['product_id'] ?? 0);
    if ($pid > 0) {
        $_SESSION['cart'][] = [
            'id' => $pid,
            'name' => '',
            'price' => 0,
            'quantity' => 1,
            'extras' => [],
            'final_price' => 0
        ];
        $_SESSION['toast_message'] = [
            'text' => 'Producto añadido al carrito',
            'icon' => 'OK'
        ];
    }

    header('Location: carta.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zyma - Carta</title>
<link rel="icon" type="image/png" href="assets/favicon.png">
<link rel="stylesheet" href="styles.css?v=20260227-2">
</head>
<body>
<header class="landing-header">
  <div class="landing-bar">
    <?php if ($guestMode): ?>
      <a href="index.php" class="landing-logo">
        <span class="landing-logo-text">Zyma</span>
      </a>
      <div class="landing-actions">
        <a href="login.php" class="landing-link" data-i18n="nav.enter">Entrar</a>
        <a href="registro.php" class="landing-cta" data-i18n="nav.createAccount">Crear cuenta</a>
      </div>
    <?php else: ?>
      <div class="profile-section">
        <button class="profile-btn" id="profileBtn" aria-label="Perfil">
          <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="white">
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4 1.79 4 4 4zm0 2c1.52 0 5.1 1.34 5.1 5v1H6.9v-1c0-3.66 3.58-5 5.1-5z"/>
          </svg>
        </button>
        <span class="user-name"><?= htmlspecialchars($display_name) ?></span>
        <div class="dropdown" id="dropdownMenu">
          <a href="perfil.php" data-i18n="nav.myProfile">Mi perfil</a>
          <a href="politica_cookies.php" class="open-cookie-preferences" data-i18n="nav.customizeCookies">Personalizar cookies</a>
          <a href="logout.php" data-i18n="nav.logout">Cerrar Sesión</a>
        </div>
      </div>

      <a href="usuario.php" class="landing-logo">
        <span class="landing-logo-text">Zyma</span>
      </a>

      <div class="quick-menu-section">
        <button class="quick-menu-btn" id="quickMenuBtn" data-i18n-aria="nav.quickMenu" aria-label="Menú rápido"></button>
        <div class="dropdown quick-dropdown" id="quickDropdown">
          <a href="usuario.php" data-i18n="nav.home">Inicio</a>
          <a href="carta.php" data-i18n="nav.viewMenu">Ver carta</a>
          <a href="valoraciones.php" data-i18n="nav.reviews">Valoraciones</a>
          <a href="incidencias.php" data-i18n="nav.incidents">Incidencias</a>
          <a href="tickets.php" data-i18n="nav.tickets">Tickets</a>
        </div>
      </div>
      <div class="cart-section">
        <a href="notificaciones.php" class="cart-btn" data-i18n-aria="nav.notifications" aria-label="Notificaciones">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="white">
            <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6V11c0-3.07-1.64-5.64-4.5-6.32V4a1.5 1.5 0 1 0-3 0v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
          </svg>
          <?php if ($unread_count > 0): ?>
            <span class="cart-count"><?= $unread_count ?></span>
          <?php endif; ?>
        </a>
        <a href="carrito.php" class="cart-btn" data-i18n-aria="nav.cart" aria-label="Carrito">
          <img src="assets/cart-icon.png" alt="Carrito">
          <span class="cart-count"><?= $cart_count ?></span>
        </a>
      </div>
    <?php endif; ?>
  </div>
</header>

<div class="container">
  <div class="hero">
    <h1 class="hero-title" data-i18n="menu.title">Carta de Zyma</h1>
    <p class="hero-sub" <?= $selectedProductId > 0 ? 'data-i18n="menu.subtitleFeatured"' : 'data-i18n="menu.subtitle"' ?>><?= $selectedProductId > 0 ? 'Vista destacada de nuestro producto estrella.' : 'Disfruta de nuestros deliciosos platos artesanales.' ?></p>
    <?php if ($starProductId > 0): ?>
      <p class="menu-star-callout"><span data-i18n="menu.starCallout">Producto estrella:</span> <a href="#producto-<?= $starProductId ?>"><?= htmlspecialchars($starProductName) ?></a></p>
    <?php endif; ?>
    <?php if ($selectedProductId > 0): ?>
      <p class="menu-star-callout"><a href="carta.php" data-i18n="menu.backToAll">Volver a toda la carta</a></p>
    <?php endif; ?>
    <?php if ($guestMode): ?>
      <p class="muted mt-1" data-i18n="menu.guestMode">Modo invitado: puedes ver la carta, para pedir necesitas iniciar Sesión.</p>
    <?php endif; ?>
  </div>

  <div class="grid grid-products">
    <?php foreach ($products as $product): ?>
    <div class="card-product <?= ((int)$product['id'] === $starProductId) ? 'card-product-star' : '' ?>" id="producto-<?= (int)$product['id'] ?>">
      <img src="<?= htmlspecialchars($product['image']) ?>"
           alt="<?= htmlspecialchars($product['name']) ?>"
           onerror="this.src='assets/default-product.png';">

      <div class="card-content">
        <?php if ((int)$product['id'] === $starProductId): ?>
          <span class="product-star-badge" data-i18n="menu.starBadge">Producto estrella</span>
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
          <?php if (!empty($product['allergens'])): ?>
            <?php foreach ($product['allergens'] as $allergen): ?>
              <span class="allergen-tag"><?= ($allergen['icon'] ?? '') . ' ' . htmlspecialchars($allergen['name']) ?></span>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <?php if ($guestMode): ?>
          <a href="login.php" class="btn-add-cart" data-i18n="menu.loginToOrder">Inicia Sesión para pedir</a>
          <a href="login.php" class="btn-valorar" data-i18n="menu.rateProduct">Valorar producto</a>
        <?php else: ?>
          <button type="button" class="btn-customize" onclick="openCustomizer(<?= (int)$product['id'] ?>)">Personalizar</button>
          <a href="valoraciones.php?producto=<?= (int)$product['id'] ?>" class="btn-valorar">Valorar</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Modal Personalizador -->
<div id="customizerModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close-modal" onclick="closeCustomizer()">&times;</span>
    <h2 class="card-title" id="customizerTitle"></h2>
    <p class="card-desc" id="customizerDesc"></p>
    
    <form method="POST" action="procesar_pedido_personalizado.php" id="customizerForm">
      <input type="hidden" name="product_id" id="customizerProductId">
      
      <div class="customizer-section">
        <h3>Extras</h3>
        <div id="customizerExtras"></div>
      </div>

      <div class="customizer-section">
        <h3>Alérgenos</h3>
        <div id="customizerAllergens"></div>
      </div>

      <div class="customizer-section">
        <h3>Cantidad</h3>
        <input type="number" name="quantity" id="customizerQuantity" value="1" min="1" max="10" style="width:60px;">
      </div>

      <div class="customizer-summary">
        <strong>Total: €<span id="customizerTotal">0.00</span></strong>
      </div>

      <button type="submit" class="btn-add-cart">Añadir al carrito</button>
    </form>
  </div>
</div>

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
<?php endif; ?>

const productsData = <?= json_encode($products) ?>;

function openCustomizer(productId) {
  console.log('openCustomizer called with ID:', productId);
  const product = productsData.find(p => p.id === productId);
  console.log('Product found:', product);
  if (!product) {
    console.error('Product not found');
    return;
  }

  document.getElementById('customizerProductId').value = productId;
  document.getElementById('customizerTitle').textContent = product.name;
  document.getElementById('customizerDesc').textContent = 'EUR ' + product.price.toFixed(2);
  document.getElementById('customizerQuantity').value = 1;

  // Render extras
  const extrasContainer = document.getElementById('customizerExtras');
  extrasContainer.innerHTML = '';
  if (product.extras && product.extras.length > 0) {
    product.extras.forEach(function(extra) {
      const div = document.createElement('div');
      div.className = 'customizer-item';
      div.innerHTML = '<label><input type="checkbox" name="extras[]" value="' + extra.id + '" data-price="' + extra.price + '"><span>' + extra.name + ' (+€' + extra.price.toFixed(2) + ')</span></label>';
      extrasContainer.appendChild(div);
    });
    
    // Add event listeners after creating checkboxes
    extrasContainer.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
      checkbox.addEventListener('change', updateCustomizerTotal);
    });
  } else {
    extrasContainer.innerHTML = '<p>Sin extras disponibles.</p>';
  }

  // Render allergens
  const allergensContainer = document.getElementById('customizerAllergens');
  allergensContainer.innerHTML = '';
  if (product.allergens && product.allergens.length > 0) {
    product.allergens.forEach(function(allergen) {
      const span = document.createElement('span');
      span.className = 'allergen-tag';
      span.textContent = (allergen.icon || '') + ' ' + allergen.name;
      allergensContainer.appendChild(span);
    });
  }

  updateCustomizerTotal();
  document.getElementById('customizerModal').style.display = 'block';
}

function closeCustomizer() {
  document.getElementById('customizerModal').style.display = 'none';
}

function updateCustomizerTotal() {
  const productId = parseInt(document.getElementById('customizerProductId').value);
  const product = productsData.find(p => p.id === productId);
  if (!product) return;

  let total = product.price;
  const quantity = parseInt(document.getElementById('customizerQuantity').value) || 1;
  
  const checkedExtras = document.querySelectorAll('input[name="extras[]"]:checked');
  checkedExtras.forEach(function(input) {
    total += parseFloat(input.dataset.price);
  });

  total *= quantity;
  document.getElementById('customizerTotal').textContent = total.toFixed(2);
}

document.getElementById('customizerQuantity').addEventListener('change', updateCustomizerTotal);

// Close modal on outside click
document.getElementById('customizerModal').addEventListener('click', function(e) {
  if (e.target.id === 'customizerModal') closeCustomizer();
});

const toast = document.getElementById('toastNotification');
if (toast) {
  setTimeout(() => toast.classList.add('show'), 100);
  setTimeout(() => toast.classList.remove('show'), 3000);
}
</script>
<script src="assets/mobile-header.js?v=20260211-6"></script>
<script src="assets/lang.js?v=20260428-1"></script>
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
</body>
</html>

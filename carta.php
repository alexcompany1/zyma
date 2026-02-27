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

if (!$guestMode && !isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
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
    $stmt = $pdo->query("SELECT id, nombre, precio, imagen FROM productos ORDER BY nombre");
    $products_db = $stmt->fetchAll();

    $products = [];
    foreach ($products_db as $product) {
        $products[] = [
            'id' => $product['id'],
            'name' => $product['nombre'],
            'description' => '',
            'price' => (float)$product['precio'],
            'image' => $product['imagen'],
            'allergens' => []
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
        ['id' => 7, 'name' => 'Agua Mineral', 'description' => 'Agua pura y natural, sin gas.', 'price' => 1.50, 'image' => 'assets/water.png', 'allergens' => []]
    ];
}

if (!$guestMode && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $pid = (int)($_POST['product_id'] ?? 0);
    if ($pid > 0) {
        $_SESSION['cart'][$pid] = ($_SESSION['cart'][$pid] ?? 0) + 1;
        $_SESSION['toast_message'] = [
            'text' => 'Producto agregado al carrito',
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
<link rel="stylesheet" href="styles.css?v=20260211-5">
</head>
<body>
<header class="landing-header">
  <div class="landing-bar">
    <?php if ($guestMode): ?>
      <a href="index.php" class="landing-logo">
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
  <div class="hero">
    <h1 class="hero-title">Carta de Zyma</h1>
    <p class="hero-sub">Disfruta de nuestros deliciosos platos artesanales.</p>
    <?php if ($guestMode): ?>
      <p class="muted mt-1">Modo invitado: puedes ver la carta, para pedir necesitas iniciar sesion.</p>
    <?php endif; ?>
  </div>

  <div class="grid grid-products">
    <?php foreach ($products as $product): ?>
    <div class="card-product">
      <img src="<?= htmlspecialchars($product['image']) ?>"
           alt="<?= htmlspecialchars($product['name']) ?>"
           onerror="this.src='assets/default-product.png';">
      <div class="card-content">
        <h2 class="card-title"><?= htmlspecialchars($product['name']) ?></h2>
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
          <a href="login.php" class="btn-add-cart">Inicia sesion para pedir</a>
        <?php else: ?>
          <form method="POST">
            <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
            <button type="submit" name="add_to_cart" class="btn-add-cart">Anadir al carrito</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php if (!$guestMode && isset($_SESSION['toast_message'])): ?>
<div class="toast-notification" id="toastNotification">
  <div class="toast-icon"><?= $_SESSION['toast_message']['icon'] ?></div>
  <span><?= $_SESSION['toast_message']['text'] ?></span>
</div>
<?php unset($_SESSION['toast_message']); endif; ?>

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
<script src="assets/mobile-header.js?v=20260211-6"></script>
</body>
</html>

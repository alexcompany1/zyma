<?php
/**
 * carta.php
 * Muestra la carta y permite agregar al carrito.
 */

// Configuracion BD
require_once 'config.php';

// Iniciar sesion
session_start();

// Validar sesion
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Crear carrito si falta
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Cargar productos
try {
    $stmt = $pdo->query("SELECT id, nombre, precio, imagen FROM productos ORDER BY nombre");
    $products_db = $stmt->fetchAll();
    
    // Adaptar a formato carrito
    $products = [];
    foreach ($products_db as $product) {
        $products[] = [
            'id' => $product['id'],
            'name' => $product['nombre'],
            'description' => '', // descripcion opcional
            'price' => floatval($product['precio']),
            'image' => $product['imagen'],
            'allergens' => [] // alergias opcional
        ];
    }
} catch (Exception $e) {
    // Si falla, usar datos locales
    $products = [
        ['id' => 1, 'name' => 'Nachos con Queso', 'description' => 'Tortillas crujientes cubiertas con queso fundido y jalapeños.', 'price' => 6.00, 'image' => 'assets/nachos.png', 'allergens' => ['gluten', 'lácteos']],
        ['id' => 2, 'name' => 'Patatas Fritas', 'description' => 'Crujientes y doradas, servidas con sal marina.', 'price' => 3.50, 'image' => 'assets/fries.png', 'allergens' => []],
        ['id' => 3, 'name' => 'Hotdog BBQ', 'description' => 'Salchicha ahumada, salsa BBQ casera, cebolla caramelizada y queso derretido.', 'price' => 7.50, 'image' => 'assets/bbq_hotdog.png', 'allergens' => ['gluten', 'lácteos', 'soja']],
        ['id' => 4, 'name' => 'Hotdog Clásico', 'description' => 'Pan artesanal, salchicha premium, mostaza y cebolla crujiente.', 'price' => 5.99, 'image' => 'assets/hotdog.png', 'allergens' => ['gluten', 'soja']],
        ['id' => 5, 'name' => 'Hotdog Vegano', 'description' => 'Salchicha vegetal, mayonesa vegana, pepinillos y mostaza.', 'price' => 6.50, 'image' => 'assets/vegan-hotdog.png', 'allergens' => ['gluten', 'soja']],
        ['id' => 6, 'name' => 'Refresco Cola', 'description' => 'Bebida refrescante y burbujeante.', 'price' => 2.00, 'image' => 'assets/soda.png', 'allergens' => []],
        ['id' => 7, 'name' => 'Agua Mineral', 'description' => 'Agua pura y natural, sin gas.', 'price' => 1.50, 'image' => 'assets/water.png', 'allergens' => []]
    ];
}

// Agregar al carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $pid = intval($_POST['product_id']);
    $_SESSION['cart'][$pid] = ($_SESSION['cart'][$pid] ?? 0) + 1;

    $_SESSION['toast_message'] = [
        'text' => 'Producto agregado al carrito',
        'icon' => '✓'
    ];

    header('Location: carta.php'); // volver a la carta
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zyma - Carta</title>
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
    <div class="header-content"><a href="usuario.php" class="logo">Zyma</a></div>
    <div class="cart-section">
        <a href="carrito.php" class="cart-btn">
            <img src="assets/cart-icon.png" alt="Carrito">
            <span class="cart-count"><?= count($_SESSION['cart'] ?? []) ?></span>
        </a>
    </div>
</header>

<div class="container">
    <div class="hero">
        <h1 class="hero-title">Carta de Zyma</h1>
        <p class="hero-sub">Disfruta de nuestros deliciosos platos artesanales.</p>
    </div>

    <!-- Productos -->
    <div class="grid grid-products">
        <?php foreach($products as $product): ?>
        <div class="card-product">
            <img src="<?= htmlspecialchars($product['image']) ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>"
                 onerror="this.src='assets/default-product.png';">
            <div class="card-content">
                <h2 class="card-title"><?= htmlspecialchars($product['name']) ?></h2>
                <p class="card-description"><?= htmlspecialchars($product['description']) ?></p>
                <p class="card-price">€<?= number_format($product['price'], 2, ',', '.') ?></p>
                
                <div class="allergen-tags">
                    <?php if(in_array('gluten', $product['allergens'])): ?>
                        <span class="allergen-tag tag-gluten">Gluten</span>
                    <?php endif; ?>
                    <?php if(in_array('lácteos', $product['allergens'])): ?>
                        <span class="allergen-tag tag-lacteos">Lácteos</span>
                    <?php endif; ?>
                    <?php if(in_array('soja', $product['allergens'])): ?>
                        <span class="allergen-tag tag-soja">Soja</span>
                    <?php endif; ?>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <button type="submit" name="add_to_cart" class="btn-add-cart">Añadir al carrito</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php if (isset($_SESSION['toast_message'])): ?>
<div class="toast-notification" id="toastNotification">
    <div class="toast-icon"><?= $_SESSION['toast_message']['icon'] ?></div>
    <span><?= $_SESSION['toast_message']['text'] ?></span>
</div>
<?php unset($_SESSION['toast_message']); endif; ?>

<script>
const profileBtn = document.getElementById('profileBtn');
const dropdownMenu = document.getElementById('dropdownMenu');
profileBtn.addEventListener('click', () => dropdownMenu.classList.toggle('show'));
window.addEventListener('click', e => {
    if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove('show');
    }
});

// Mostrar notificacion
const toast = document.getElementById('toastNotification');
if (toast) {
    setTimeout(() => {
        toast.classList.add('show'); // mostrar
    }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show'); // ocultar
    }, 3000);
}
</script>
</body>
</html>


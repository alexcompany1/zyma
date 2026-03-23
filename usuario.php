<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config.php';

$show_cookie_popup = !empty($_SESSION['show_cookie_popup']);
$cookie_preferences = $_SESSION['cookie_preferences'] ?? [];
unset($_SESSION['show_cookie_popup']);

$display_name = trim($_SESSION['nombre'] ?? '');
if ($display_name === '') {
    $display_name = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? '');
}

$cart_count = count($_SESSION['cart'] ?? []);
$first_name = trim(explode(' ', $display_name)[0] ?? $display_name);

$featuredProducts = [];
$resenias = [];
$starProduct = null;
$stats = [
    'productos' => 0,
    'valoraciones' => 0,
    'promedio' => 0,
];

try {
    $statsQuery = $pdo->query("
        SELECT
            (SELECT COUNT(*) FROM productos) AS total_productos,
            (SELECT COUNT(*) FROM valoraciones) AS total_valoraciones,
            (SELECT ROUND(AVG(puntuacion), 1) FROM valoraciones) AS promedio_valoraciones
    ");
    $statsRow = $statsQuery->fetch(PDO::FETCH_ASSOC) ?: [];
    $stats['productos'] = (int) ($statsRow['total_productos'] ?? 0);
    $stats['valoraciones'] = (int) ($statsRow['total_valoraciones'] ?? 0);
    $stats['promedio'] = (float) ($statsRow['promedio_valoraciones'] ?? 0);
} catch (Exception $e) {
    $stats = [
        'productos' => 0,
        'valoraciones' => 0,
        'promedio' => 0,
    ];
}

try {
    $stmt = $pdo->query("
        SELECT
            p.id,
            p.nombre,
            p.descripcion,
            p.precio,
            p.imagen,
            ROUND(AVG(v.puntuacion), 1) AS promedio,
            COUNT(v.id) AS total_valoraciones
        FROM productos p
        LEFT JOIN valoraciones v ON v.id_producto = p.id
        GROUP BY p.id, p.nombre, p.descripcion, p.precio, p.imagen
        ORDER BY
            CASE
                WHEN p.nombre LIKE '%Hotdog%' OR p.nombre LIKE '%hotdog%' THEN 0
                ELSE 1
            END,
            promedio DESC,
            total_valoraciones DESC,
            p.nombre ASC
        LIMIT 3
    ");
    $featuredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $starProduct = $featuredProducts[0] ?? null;
} catch (Exception $e) {
    $featuredProducts = [];
    $starProduct = null;
}

try {
    $stmt = $pdo->query("
        SELECT
            v.puntuacion,
            v.comentario,
            u.nombre AS nombre,
            p.nombre AS producto
        FROM valoraciones v
        INNER JOIN usuarios u ON u.id = v.id_usuario
        INNER JOIN productos p ON p.id = v.id_producto
        WHERE v.comentario IS NOT NULL
          AND TRIM(v.comentario) <> ''
          AND v.puntuacion >= 4
        ORDER BY v.fecha_creacion DESC
        LIMIT 6
    ");
    $resenias = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    $resenias = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zyma - Bienvenido</title>
  <link rel="stylesheet" href="styles.css?v=20260320-2">
</head>
<body>
<header class="landing-header">
  <div class="landing-bar">
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
      <a href="carrito.php" class="cart-btn" aria-label="Carrito">
        <img src="assets/cart-icon.png" alt="Carrito">
        <span class="cart-count"><?= $cart_count ?></span>
      </a>
    </div>
  </div>
</header>

<main class="container user-home">
  <section class="user-home-hero">
    <div class="user-home-copy">
      <span class="user-home-kicker">Bienvenido a Zyma</span>
      <div class="user-home-greeting">
        <span class="user-home-greeting-label">Panel personal</span>
        <h1>
          <span class="user-home-greeting-text">Hola,</span>
          <span class="user-home-greeting-name"><?= htmlspecialchars($first_name) ?></span>
        </h1>
      </div>
      <p>Descubre una experiencia mas cuidada, con acceso rapido a la carta, valoraciones reales y un panel mucho mas limpio para moverte por la web.</p>

      <div class="user-home-actions">
        <a href="carta.php" class="btn-cart">Ver la carta</a>
        <?php if ($starProduct): ?>
          <a href="carta.php?producto=<?= urlencode((string) $starProduct['id']) ?>" class="user-home-secondary-btn">Producto estrella</a>
        <?php endif; ?>
        <a href="mis_pedidos.php" class="user-home-secondary-btn">Mis pedidos</a>
      </div>

      <div class="user-home-stats">
        <article class="user-stat-card">
          <strong><?= $stats['productos'] ?></strong>
          <span>productos disponibles</span>
        </article>
        <article class="user-stat-card">
          <strong><?= $stats['valoraciones'] ?></strong>
          <span>valoraciones reales</span>
        </article>
        <article class="user-stat-card">
          <strong><?= $stats['promedio'] > 0 ? number_format($stats['promedio'], 1) : '5.0' ?></strong>
          <span>media de satisfaccion</span>
        </article>
      </div>
    </div>

    <aside class="user-home-spotlight">
      <span class="user-home-card-kicker">Acceso rapido</span>
      <h2>Todo lo importante en un vistazo</h2>
      <ul class="user-home-shortcuts">
        <?php if ($starProduct): ?>
          <li><a href="carta.php?producto=<?= urlencode((string) $starProduct['id']) ?>">Ir al producto estrella</a></li>
        <?php endif; ?>
        <li><a href="carta.php">Explorar carta completa</a></li>
        <li><a href="valoraciones.php">Ver opiniones de clientes</a></li>
        <li><a href="tickets.php">Abrir o revisar tickets</a></li>
        <li><a href="perfil.php">Actualizar perfil</a></li>
      </ul>
      <div class="user-home-note">
        <strong>Consejo</strong>
        <p>Empieza por la carta para descubrir los productos mejor valorados y anadirlos al carrito en pocos pasos.</p>
      </div>
    </aside>
  </section>

  <section class="user-home-links">
    <?php if ($starProduct): ?>
      <a class="user-link-card user-link-card-star" href="carta.php?producto=<?= urlencode((string) $starProduct['id']) ?>">
        <span class="user-link-tag">Producto estrella</span>
        <h3><?= htmlspecialchars($starProduct['nombre']) ?></h3>
        <p>Accede directamente al hotdog estrella del momento en una vista dedicada solo para ese producto.</p>
      </a>
    <?php endif; ?>
    <a class="user-link-card" href="<?= $starProduct ? 'carta.php?producto=' . urlencode((string) $starProduct['id']) : 'carta.php' ?>">
      <span class="user-link-tag">Carta</span>
      <h3>Descubre el producto estrella</h3>
      <p>Entra directamente a la vista del producto destacado y revisalo sin distracciones.</p>
    </a>
    <a class="user-link-card" href="valoraciones.php">
      <span class="user-link-tag">Opiniones</span>
      <h3>Revisa lo que opinan otros clientes</h3>
      <p>Conoce las reseñas recientes y valora tus productos favoritos.</p>
    </a>
    <a class="user-link-card" href="tickets.php">
      <span class="user-link-tag">Soporte</span>
      <h3>Gestiona dudas o incidencias</h3>
      <p>Accede a tus tickets y mantente al dia con las respuestas.</p>
    </a>
  </section>

  <?php if (!empty($featuredProducts)): ?>
    <section class="user-home-section">
      <div class="user-home-section-head">
        <div>
          <span class="user-home-card-kicker">Destacados</span>
          <h2>Productos que mejor impresion causan</h2>
        </div>
        <a href="carta.php" class="user-home-inline-link">Ver toda la carta</a>
      </div>

      <div class="user-featured-grid">
        <?php foreach ($featuredProducts as $product): ?>
          <article class="user-featured-card">
            <div class="user-featured-image">
              <?php if (!empty($product['imagen'])): ?>
                <img src="<?= htmlspecialchars($product['imagen']) ?>" alt="<?= htmlspecialchars($product['nombre']) ?>">
              <?php else: ?>
                <div class="user-featured-image-fallback">Zyma</div>
              <?php endif; ?>
            </div>
            <div class="user-featured-content">
              <div class="user-featured-top">
                <h3><?= htmlspecialchars($product['nombre']) ?></h3>
                <span class="user-featured-price"><?= number_format((float) $product['precio'], 2, ',', '.') ?> EUR</span>
              </div>
              <p><?= htmlspecialchars($product['descripcion'] ?: 'Producto destacado de la casa con preparacion cuidada y sabor potente.') ?></p>
              <div class="user-featured-meta">
                <span><?= number_format((float) ($product['promedio'] ?? 0), 1) ?> / 5</span>
                <span><?= (int) ($product['total_valoraciones'] ?? 0) ?> valoraciones</span>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if (!empty($resenias)): ?>
    <section class="user-home-section user-home-reviews">
      <div class="user-home-section-head">
        <div>
          <span class="user-home-card-kicker">Resenas</span>
          <h2>Lo que dicen nuestros clientes</h2>
        </div>
        <a href="valoraciones.php" class="user-home-inline-link">Ver todas</a>
      </div>

      <div class="user-reviews-grid">
        <?php foreach ($resenias as $resena): ?>
          <article class="review-card user-review-card">
            <div class="review-stars">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <img src="assets/<?= $i <= $resena['puntuacion'] ? 'estrellaSelecionada' : 'estrellaNegra' ?>.png" alt="Estrella">
              <?php endfor; ?>
            </div>
            <p class="user-review-text">"<?= htmlspecialchars($resena['comentario']) ?>"</p>
            <div class="review-author">
              <strong><?= htmlspecialchars($resena['nombre']) ?></strong>
              <span class="review-product"><?= htmlspecialchars($resena['producto']) ?></span>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</main>

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

<?php include 'cookie_popup.php'; ?>

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
</body>
</html>


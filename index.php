<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener productos destacados
$productosDestacados = [];
try {
    $stmt = $pdo->query("
        SELECT p.id, p.nombre, p.precio, p.imagen, p.descripcion,
               ROUND(AVG(v.puntuacion), 1) as promedio,
               COUNT(v.id) as total_valoraciones
        FROM productos p
        LEFT JOIN valoraciones v ON p.id = v.id_producto
        WHERE p.disponible = 1
        GROUP BY p.id
        ORDER BY promedio DESC, total_valoraciones DESC
        LIMIT 3
    ");
    $productosDestacados = $stmt->fetchAll();
} catch (Exception $e) {
    $productosDestacados = [];
}

// Obtener reseñas destacadas
$resenas = [];
try {
    $stmt = $pdo->query("
        SELECT v.puntuacion, v.comentario, v.fecha_creacion,
               u.nombre as nombre_usuario, p.nombre as nombre_producto
        FROM valoraciones v
        JOIN usuarios u ON v.id_usuario = u.id
        JOIN productos p ON v.id_producto = p.id
        WHERE v.comentario IS NOT NULL AND v.comentario != '' AND v.puntuacion >= 4
        ORDER BY v.fecha_creacion DESC
        LIMIT 10
    ");
    $resenas = $stmt->fetchAll();
} catch (Exception $e) {
    $resenas = [];
}
?>
<?php
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($basePath === '.' || $basePath === '/') {
  $basePath = '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zyma</title>
  <link rel="stylesheet" href="<?= $basePath ?>/styles.css?v=20260513-1">
</head>
<body class="page-enter">
  <header class="landing-header">
    <div class="landing-bar">
      <a href="index.php" class="landing-logo">
        <img src="assets/zyma.jpg" alt="Zyma" class="landing-logo-img">
        <span class="landing-logo-text">Zyma</span>
      </a>
      <nav class="landing-nav">
        <a href="<?= $basePath ?>/carta.php?guest=1" class="landing-nav-link">Carta</a>
        <a href="#como-funciona" class="landing-nav-link">Como funciona</a>
        <a href="#opiniones" class="landing-nav-link">Opiniones</a>
      </nav>
      <div class="landing-actions">
        <a href="<?= $basePath ?>/login.php" class="landing-link">Entrar</a>
        <a href="<?= $basePath ?>/registro.php" class="landing-cta">Crear cuenta</a>
      </div>
    </div>
  </header>
  <div class="container">

    <section class="landing-hero-section">
      <div class="landing-hero-card reveal">
        <span class="landing-kicker" data-i18n="landing.kicker">Hotdogs artesanales</span>
        <h1 class="landing-hero-title" data-i18n="landing.tagline">Zyma. Sabor con alma.</h1>
        <p class="landing-hero-text" data-i18n-raw="1" data-i18n="landing.heroText">
          Recetas de la casa, ingredientes frescos y un sabor que no se olvida.
          Crea tu cuenta o inicia sesion y descubre nuestra carta.
        </p>
        <div class="landing-hero-actions">
          <a href="<?= $basePath ?>/registro.php" class="landing-hero-btn primary btn-ripple" data-i18n="landing.createAccount">Crear cuenta</a>
          <a href="<?= $basePath ?>/login.php" class="landing-hero-btn secondary btn-ripple" data-i18n="landing.loginBtn">Ya tengo cuenta</a>
          <a href="<?= $basePath ?>/acceso_invitado.php" class="landing-hero-btn ghost" data-i18n="landing.guestMode">Ver carta sin cuenta</a>
        </div>
      </div>
      <div class="landing-hero-visual reveal-right" data-delay="200">
        <div class="landing-stats-grid">
          <div class="landing-stat">
            <strong class="count-up" data-target="100" data-suffix="%">100%</strong>
            <span>Ingredientes frescos</span>
          </div>
          <div class="landing-stat">
            <strong class="count-up" data-target="48" data-prefix="" data-suffix="" data-duration="2000">4.8</strong>
            <span>Valoracion media</span>
          </div>
          <div class="landing-stat">
            <strong>24h</strong>
            <span>Pedidos rapidos</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Productos destacados -->
    <?php if (!empty($productosDestacados)): ?>
    <section class="landing-section reveal">
      <div class="landing-section-head">
        <div>
        <span class="landing-section-kicker" data-i18n="landing.mostOrdered">Lo mas pedido</span>
          <h2 class="landing-section-title" data-i18n="landing.featuredProducts">Productos destacados</h2>
         </div>
         <a href="<?= $basePath ?>/carta.php?guest=1" class="landing-section-link" data-i18n="landing.seeFullMenu">Ver toda la carta</a>
      </div>
      <div class="landing-featured-grid reveal-stagger" data-stagger-delay="120">
        <?php foreach ($productosDestacados as $prod): ?>
        <a href="<?= $basePath ?>/carta.php?guest=1&producto=<?= (int)$prod['id'] ?>" class="landing-featured-card">
          <div class="landing-featured-img">
            <img src="<?= htmlspecialchars($prod['imagen']) ?>"
                 alt="<?= htmlspecialchars($prod['nombre']) ?>"
                 onerror="this.parentElement.innerHTML='<div class=\'landing-featured-fallback\'>Z</div>'">
          </div>
          <div class="landing-featured-info">
            <h3><?= htmlspecialchars($prod['nombre']) ?></h3>
            <div class="landing-featured-rating">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <img src="assets/<?= $i <= round($prod['promedio'] ?? 0) ? 'estrellaSelecionada.png' : 'estrellaNegra.png' ?>"
                     alt="" style="width:12px;height:12px;">
              <?php endfor; ?>
              <span><?= number_format((float)($prod['promedio'] ?? 0), 1) ?> (<?= (int)$prod['total_valoraciones'] ?>)</span>
            </div>
            <span class="landing-featured-price">EUR <?= number_format((float)$prod['precio'], 2, ',', '.') ?></span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- Carrusel de resenas -->
    <?php if (!empty($resenas)): ?>
    <section class="landing-section reviews-section reveal">
      <div class="landing-section-head center-head">
        <div>
          <span class="landing-section-kicker" data-i18n="landing.realReviews">Opiniones reales</span>
          <h2 class="landing-section-title" data-i18n="landing.whatClientsSay">Lo que dicen nuestros clientes</h2>
        </div>
      </div>
      <div class="reviews-carousel-container">
        <button class="carousel-arrow carousel-prev" id="carouselPrev">&#10094;</button>
        <div class="reviews-carousel" id="reviewsCarousel">
          <?php foreach ($resenas as $resena): ?>
          <div class="review-card">
            <div class="review-stars">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <img src="assets/<?= $i <= $resena['puntuacion'] ? 'estrellaSelecionada.png' : 'estrellaNegra.png' ?>"
                     alt="Estrella" style="width:14px;height:14px;">
              <?php endfor; ?>
            </div>
            <p class="review-comment">"<?= htmlspecialchars($resena['comentario']) ?>"</p>
            <p class="review-product"><?= htmlspecialchars($resena['nombre_producto']) ?></p>
            <p class="review-author">— <?= htmlspecialchars($resena['nombre_usuario'] ?: 'Cliente') ?></p>
          </div>
          <?php endforeach; ?>
        </div>
        <button class="carousel-arrow carousel-next" id="carouselNext">&#10095;</button>
      </div>
    </section>
    <?php endif; ?>

    <!-- Seccion como funciona -->
    <section class="landing-section reveal">
      <div class="landing-section-head center-head">
        <div>
          <span class="landing-section-kicker" data-i18n="landing.simpleFast">Sencillo y rapido</span>
          <h2 class="landing-section-title" data-i18n="landing.howItWorks">Como funciona</h2>
        </div>
      </div>
      <div class="landing-steps-grid reveal-stagger" data-stagger-delay="150">
        <div class="landing-step-card">
          <div class="landing-step-number">1</div>
          <h3 data-i18n="landing.step1Title">Crea tu cuenta</h3>
          <p data-i18n="landing.step1Text">Registrate en menos de un minuto y accede a todas las funcionalidades.</p>
        </div>
        <div class="landing-step-card">
          <div class="landing-step-number">2</div>
          <h3 data-i18n="landing.step2Title">Explora la carta</h3>
          <p data-i18n="landing.step2Text">Descubre nuestros hotdogs artesanales, entrantes y bebidas.</p>
        </div>
        <div class="landing-step-card">
          <div class="landing-step-number">3</div>
          <h3 data-i18n="landing.step3Title">Haz tu pedido</h3>
          <p data-i18n="landing.step3Text">Anade al carrito, elige tu metodo de pago y confirma tu pedido.</p>
        </div>
      </div>
    </section>

    <footer>
      <p data-i18n="common.street"> Calle Falsa 123, Barcelona</p>
      <p data-i18n="common.contact"> contacto@zyma.com | +34 600 000 000</p>
      <p class="social-links">
        <a href="#" data-i18n="common.facebook">Facebook</a> ·
        <a href="#" data-i18n="common.instagram">Instagram</a> ·
        <a href="#" data-i18n="common.twitter">Twitter</a>
      </p>
      <p class="footer-legal-links">
        <a href="politica_cookies.php" data-i18n="common.cookiePolicy">Politica de Cookies</a>
        <span>|</span>
        <a href="politica_privacidad.php" data-i18n="common.privacyPolicy">Politica de Privacidad</a>
        <span>|</span>
        <a href="aviso_legal.php" data-i18n="common.legalNotice">Aviso Legal</a>
      </p>
    </footer>
  </div>
  <script src="<?= $basePath ?>/assets/mobile-header.js?v=20260513-1"></script>
  <script src="<?= $basePath ?>/assets/animations.js?v=20260513-1" defer></script>
<?php require_once 'language_selector.php'; ?>
</body>
</html>

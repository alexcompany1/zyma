<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

require_once 'config.php';

// Obtener reseñas destacadas (últimas con comentario y puntuación alta)
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
  <link rel="stylesheet" href="<?= $basePath ?>/styles.css?v=20260227-3">
</head>
<body>
  <div class="container">
    <header class="landing-header">
      <div class="landing-bar">
        <a href="<?= $basePath ?>/index.php" class="landing-logo">
          <span class="landing-logo-text">Zyma</span>
        </a>
        <div class="landing-actions">
          <a href="<?= $basePath ?>/login.php" class="landing-link">Entrar</a>
          <a href="<?= $basePath ?>/registro.php" class="landing-cta">Crear cuenta</a>
        </div>
      </div>
    </header>

    <div class="landing-hero">
      <h2 class="landing-title">Zyma. Hotdogs con alma.</h2>
      
      <!-- Bienvenida -->
      <p class="welcome-text">
        Recetas de la casa, ingredientes frescos y un sabor que no se olvida. 
        Crea tu cuenta o inicia sesión y descubre la carta.
      </p>
      
      <!-- Botones principales -->
      <div class="btn-row center">
        <a href="<?= $basePath ?>/registro.php" class="register-btn">Crear cuenta</a>
        <a href="<?= $basePath ?>/login.php" class="login-btn">Ya tengo cuenta</a>
        <a href="<?= $basePath ?>/acceso_invitado.php" class="login-btn">Ver carta sin cuenta</a>
      </div>
    </div>

    <!-- Carrusel de reseñas -->
    <?php if (!empty($resenas)): ?>
    <section class="reviews-section">
      <h3 class="reviews-title">Lo que dicen nuestros clientes</h3>
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

    <footer>
      <p> Calle Falsa 123, Barcelona</p>
      <p> contacto@zyma.com | +34 600 000 000</p>
      <p class="social-links">
        <a href="#">Facebook</a> · 
        <a href="#">Instagram</a> · 
        <a href="#">Twitter</a>
      </p>
    </footer>
  </div>
<script src="<?= $basePath ?>/assets/mobile-header.js?v=20260211-6"></script>
<script>
// Carrusel de reseñas
const carousel = document.getElementById('reviewsCarousel');
const prevBtn = document.getElementById('carouselPrev');
const nextBtn = document.getElementById('carouselNext');

if (carousel && prevBtn && nextBtn) {
  const cardWidth = 280;
  const gap = 20;
  const scrollAmount = cardWidth + gap;

  prevBtn.addEventListener('click', () => {
    carousel.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
  });

  nextBtn.addEventListener('click', () => {
    carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
  });

  // Auto-scroll cada 5 segundos
  let autoScroll = setInterval(() => {
    if (carousel.scrollLeft + carousel.clientWidth >= carousel.scrollWidth) {
      carousel.scrollTo({ left: 0, behavior: 'smooth' });
    } else {
      carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
  }, 5000);

  // Pausar auto-scroll cuando el usuario interactúa
  carousel.addEventListener('mouseenter', () => clearInterval(autoScroll));
  carousel.addEventListener('mouseleave', () => {
    autoScroll = setInterval(() => {
      if (carousel.scrollLeft + carousel.clientWidth >= carousel.scrollWidth) {
        carousel.scrollTo({ left: 0, behavior: 'smooth' });
      } else {
        carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
      }
    }, 5000);
  });
}
</script>
</body>
</html>

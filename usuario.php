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

$display_name = trim($_SESSION['nombre'] ?? '');
if ($display_name === '') {
    $display_name = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? '');
}
$cart_count = count($_SESSION['cart'] ?? []);

// Obtener reseñas de la base de datos
$resenias = [];
try {
    $stmt = $pdo->prepare("
        SELECT v.puntuacion, v.comentario, u.nombre, p.nombre as producto
        FROM valoraciones v
        JOIN usuarios u ON v.id_usuario = u.id
        JOIN productos p ON v.id_producto = p.id
        WHERE v.comentario IS NOT NULL AND v.comentario != ''
        ORDER BY v.fecha_creacion DESC
        LIMIT 10
    ");
    $stmt->execute();
    $resenias = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error obteniendo reseñas: " . $e->getMessage());
    $resenias = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zyma - Restaurante de Hotdogs</title>
  <link rel="stylesheet" href="styles.css?v=20260217-1">
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

<main class="main-content">
  <div class="center">
    <h1 class="welcome">&iexcl;Hola, <?= htmlspecialchars($display_name) ?>!</h1>
    <p class="muted mt-2 mb-3 lead">
      Bienvenido a Zyma. Aqu&iacute; cada bocado tiene historia: pan artesanal, ingredientes frescos y recetas de la casa.
    </p>
    <div class="btn-row center">
      <a href="carta.php" class="btn-cart">Ver la Carta</a>
    </div>
  </div>

  <!-- Carrusel de Reseñas -->
  <?php if (!empty($resenias)): ?>
    <div class="reviews-carousel-section">
      <h2 style="text-align: center; margin-bottom: 2rem; color: var(--ink); font-size: 1.8rem;">Lo que dicen nuestros clientes</h2>
      
      <div class="reviews-carousel-container">
        <button class="carousel-btn carousel-prev" id="prevBtn" aria-label="Anterior"></button>
        
        <div class="reviews-carousel">
          <?php foreach ($resenias as $resena): ?>
            <div class="review-card">
              <div class="review-stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <img src="assets/<?= $i <= $resena['puntuacion'] ? 'estrellaSelecionada' : 'estrellaNegra' ?>.png" alt="estrella">
                <?php endfor; ?>
              </div>
              <p class="review-text">"<?= htmlspecialchars($resena['comentario']) ?>"</p>
              <div class="review-author">
                <strong><?= htmlspecialchars($resena['nombre']) ?></strong>
                <span class="review-product"><?= htmlspecialchars($resena['producto']) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <button class="carousel-btn carousel-next" id="nextBtn" aria-label="Siguiente"></button>
      </div>

      <div class="carousel-indicators">
        <?php foreach ($resenias as $idx => $resena): ?>
          <button class="indicator <?= $idx === 0 ? 'active' : '' ?>" data-slide="<?= $idx ?>"></button>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</main>

<footer>
  <p>&copy; 2025 Zyma. Todos los derechos reservados.</p>
</footer>

<script>
// Dropdown del perfil
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

// Carrusel de Reseñas
document.addEventListener('DOMContentLoaded', function() {
  const carousel = document.querySelector('.reviews-carousel');
  const cards = document.querySelectorAll('.review-card');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const indicators = document.querySelectorAll('.carousel-indicators .indicator');
  const container = document.querySelector('.reviews-carousel-container');
  
  if (!carousel || cards.length === 0) return;
  
  let currentSlide = 0;
  
  function showSlide(n) {
    currentSlide = (n + cards.length) % cards.length;
    
    // Calcular el ancho real del contenedor
    const containerWidth = container.offsetWidth;
    const translateAmount = currentSlide * containerWidth;
    
    carousel.style.transform = `translateX(-${translateAmount}px)`;
    
    indicators.forEach((ind, idx) => {
      ind.classList.toggle('active', idx === currentSlide);
    });
  }
  
  function nextSlide() {
    showSlide(currentSlide + 1);
  }
  
  function prevSlide() {
    showSlide(currentSlide - 1);
  }
  
  // Event listeners
  if (nextBtn) nextBtn.addEventListener('click', nextSlide);
  if (prevBtn) prevBtn.addEventListener('click', prevSlide);
  
  indicators.forEach((indicator, idx) => {
    indicator.addEventListener('click', () => showSlide(idx));
  });
  
  // Auto-avance cada 6 segundos
  setInterval(nextSlide, 6000);
  
  // Recalcular en caso de resize
  window.addEventListener('resize', () => showSlide(currentSlide));
});
</script>
<script src="assets/mobile-header.js?v=20260211-6"></script>
</body>
</html>

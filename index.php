<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

require_once 'config.php';
session_start();

// Check if user is logged in
$loggedIn = isset($_SESSION['user_id']);
$display_name = '';
$userRole = '';
if ($loggedIn) {
    $display_name = trim($_SESSION['nombre'] ?? '');
    if ($display_name === '') {
        $display_name = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? '');
    }
    $userRole = $_SESSION['role'] ?? 'cliente';
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
        LIMIT 6
    ");
    $resenas = $stmt->fetchAll();
} catch (Exception $e) {
    $resenas = [];
}

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
  <title>Zyma - Hotdogs Artesanales</title>
  <link rel="icon" type="image/png" href="assets/favicon.png">
  <link rel="stylesheet" href="<?= $basePath ?>/styles.css?v=20260505-1">
  <style>
    .hero-section {
      background: linear-gradient(135deg, #3f0910 0%, #6f151f 50%, #9c3a33 100%);
      border-radius: 24px;
      padding: 3rem 2rem;
      margin: 2rem auto;
      max-width: 1000px;
      color: white;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .hero-section::before {
      content: "";
      position: absolute;
      top: -50%;
      right: -10%;
      width: 300px;
      height: 300px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.05);
    }
    
    .hero-title {
      font-size: 2.5rem;
      font-weight: 800;
      margin-bottom: 1rem;
      position: relative;
      z-index: 1;
    }
    
    .hero-text {
      font-size: 1.1rem;
      color: rgba(255, 255, 255, 0.85);
      margin-bottom: 2rem;
      position: relative;
      z-index: 1;
    }
    
    .hero-actions {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
      position: relative;
      z-index: 1;
    }
    
    .hero-actions .btn-primary {
      padding: 1rem 2rem;
      background: var(--gold);
      color: var(--deep-red);
      border: none;
      border-radius: 14px;
      font-weight: 800;
      font-size: 1.05rem;
      text-decoration: none;
      transition: all 0.3s ease;
      display: inline-block;
    }
    
    .hero-actions .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 28px rgba(0,0,0,0.2);
    }
    
    .hero-actions .btn-secondary {
      padding: 1rem 2rem;
      background: rgba(255, 255, 255, 0.15);
      color: white;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-radius: 14px;
      font-weight: 700;
      font-size: 1.05rem;
      text-decoration: none;
      transition: all 0.3s ease;
      display: inline-block;
    }
    
    .hero-actions .btn-secondary:hover {
      background: rgba(255, 255, 255, 0.25);
      transform: translateY(-3px);
    }
    
    .featured-section {
      max-width: 1000px;
      margin: 3rem auto;
      padding: 0 1rem;
    }
    
    .featured-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-top: 2rem;
    }
    
    .featured-card {
      background: var(--white);
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 16px rgba(60, 16, 16, 0.08);
      border: 1px solid rgba(69, 5, 12, 0.06);
      transition: all 0.3s ease;
    }
    
    .featured-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px rgba(60, 16, 16, 0.12);
    }
    
    .featured-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }
    
    .featured-content {
      padding: 1.2rem;
    }
    
    .featured-content h3 {
      margin: 0 0 0.5rem;
      color: var(--deep-red);
      font-size: 1.1rem;
    }
    
    .featured-content .price {
      font-size: 1.2rem;
      font-weight: 800;
      color: var(--gold);
      margin-top: 0.5rem;
    }
    
    .reviews-section {
      max-width: 1000px;
      margin: 3rem auto;
      padding: 0 1rem;
    }
    
    .reviews-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
      margin-top: 2rem;
    }
    
    .review-card {
      background: var(--white);
      border-radius: 16px;
      padding: 1.5rem;
      box-shadow: 0 4px 16px rgba(60, 16, 16, 0.08);
      border: 1px solid rgba(69, 5, 12, 0.06);
    }
    
    .review-stars {
      margin-bottom: 0.8rem;
    }
    
    .review-text {
      font-style: italic;
      color: #4a5568;
      margin-bottom: 1rem;
      line-height: 1.6;
    }
    
    .review-author {
      font-weight: 600;
      color: var(--deep-red);
      font-size: 0.9rem;
    }
    
    .review-product {
      color: #718096;
      font-size: 0.85rem;
    }
    
    .stats-bar {
      display: flex;
      justify-content: center;
      gap: 3rem;
      margin: 2rem auto;
      max-width: 1000px;
      padding: 1.5rem;
      background: var(--white);
      border-radius: 16px;
      box-shadow: 0 4px 16px rgba(60, 16, 16, 0.08);
    }
    
    .stat-item {
      text-align: center;
    }
    
    .stat-value {
      font-size: 2rem;
      font-weight: 800;
      color: var(--deep-red);
    }
    
    .stat-label {
      font-size: 0.9rem;
      color: #718096;
    }
  </style>
</head>
<body>
  <div class="container">
    <header class="landing-header">
      <div class="landing-bar">
        <?php if ($loggedIn): ?>
        <div class="profile-section">
          <button class="profile-btn" id="profileBtn">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="white">
              <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c1.52 0 5.1 1.34 5.1 5v1H6.9v-1c0-3.66 3.58-5 5.1-5z"/>
            </svg>
          </button>
          <span class="user-name"><?= htmlspecialchars($display_name) ?></span>
          <div class="dropdown" id="dropdownMenu">
            <a href="<?= $userRole === 'admin' ? 'admin.php' : ($userRole === 'worker' ? 'trabajador.php' : 'perfil.php') ?>">Mi perfil</a>
            <a href="politica_cookies.php" class="open-cookie-preferences">Personalizar cookies</a>
            <a href="logout.php">Cerrar sesión</a>
          </div>
        </div>

        <a href="<?= $userRole === 'admin' ? 'admin.php' : ($userRole === 'worker' ? 'trabajador.php' : 'usuario.php') ?>" class="landing-logo">
          <span class="landing-logo-text">Zyma</span>
        </a>

        <div class="quick-menu-section">
          <button class="quick-menu-btn" id="quickMenuBtn" aria-label="Menú rápido"></button>
          <div class="dropdown quick-dropdown" id="quickDropdown">
            <a href="<?= $userRole === 'admin' ? 'admin.php' : ($userRole === 'worker' ? 'trabajador.php' : 'usuario.php') ?>">Inicio</a>
            <a href="carta.php">Ver carta</a>
            <a href="valoraciones.php">Valoraciones</a>
            <?php if ($userRole === 'cliente'): ?>
            <a href="carrito.php">Carrito</a>
            <?php endif; ?>
          </div>
        </div>
        <?php else: ?>
        <a href="<?= $basePath ?>/index.php" class="landing-logo">
          <span class="landing-logo-text">Zyma</span>
        </a>
        <div class="landing-actions">
          <a href="<?= $basePath ?>/login.php" class="landing-link">Entrar</a>
          <a href="<?= $basePath ?>/registro.php" class="landing-cta">Crear cuenta</a>
        </div>
        <?php endif; ?>
      </div>
    </header>

    <?php if (!$loggedIn): ?>
    <div class="hero-section">
      <span style="display: inline-block; background: rgba(255,255,255,0.15); padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem; margin-bottom: 1rem;">
        🌭 Hotdogs Artesanales
      </span>
      <h1 class="hero-title">Zyma. Sabor con alma.</h1>
      <p class="hero-text">
        Recetas de la casa, ingredientes frescos y un sabor que no se olvida.
        Crea tu cuenta o inicia sesión y descubre nuestra carta.
      </p>
      <div class="hero-actions">
        <a href="<?= $basePath ?>/registro.php" class="btn-primary">Crear cuenta</a>
        <a href="<?= $basePath ?>/login.php" class="btn-secondary">Ya tengo cuenta</a>
        <a href="<?= $basePath ?>/acceso_invitado.php" class="btn-secondary">Ver carta sin cuenta</a>
      </div>
    </div>
    <?php else: ?>
    <div class="hero-section">
      <h1 class="hero-title">¡Hola, <?= htmlspecialchars($display_name) ?>! 👋</h1>
      <p class="hero-text">
        Bienvenido de nuevo a Zyma. Explora nuestra carta, revisa tus pedidos y disfruta de nuestros hotdogs artesanales.
      </p>
      <div class="hero-actions">
        <a href="carta.php" class="btn-primary">Ver la carta</a>
        <?php if ($userRole === 'cliente'): ?>
        <a href="mis_pedidos.php" class="btn-secondary">Mis pedidos</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($productosDestacados)): ?>
    <div class="featured-section">
      <h2 style="text-align: center; color: var(--deep-red); font-size: 1.8rem;">Productos Destacados</h2>
      <div class="featured-grid">
        <?php foreach ($productosDestacados as $prod): ?>
        <div class="featured-card">
          <?php if (!empty($prod['imagen'])): ?>
          <img src="<?= htmlspecialchars($prod['imagen']) ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>">
          <?php else: ?>
          <div style="height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 3rem;">🌭</div>
          <?php endif; ?>
          <div class="featured-content">
            <h3><?= htmlspecialchars($prod['nombre']) ?></h3>
            <p style="color: #718096; font-size: 0.9rem; margin: 0.5rem 0;">
              <?= htmlspecialchars(substr($prod['descripcion'] ?? '', 0, 80)) ?>...
            </p>
            <div class="price">€ <?= number_format((float)$prod['precio'], 2, ',', '.') ?></div>
            <?php if (($prod['promedio'] ?? 0) > 0): ?>
            <div style="margin-top: 0.5rem;">
              <?php for ($i = 1; $i <= 5; $i++): ?>
              <span style="color: <?= $i <= round($prod['promedio']) ? '#fbbf24' : '#e5e7eb' ?>;">★</span>
              <?php endfor; ?>
              <span style="font-size: 0.85rem; color: #718096;">(<?= $prod['total_valoraciones'] ?>)</span>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($resenas)): ?>
    <div class="reviews-section">
      <h2 style="text-align: center; color: var(--deep-red); font-size: 1.8rem;">Lo que dicen nuestros clientes ⭐</h2>
      <div class="reviews-grid">
        <?php foreach ($resenas as $resena): ?>
        <div class="review-card">
          <div class="review-stars">
            <?php for ($i = 1; $i <= 5; $i++): ?>
            <span style="color: <?= $i <= $resena['puntuacion'] ? '#fbbf24' : '#e5e7eb' ?>; font-size: 1.2rem;">★</span>
            <?php endfor; ?>
          </div>
          <p class="review-text">"<?= htmlspecialchars($resena['comentario']) ?>"</p>
          <p class="review-product">📦 <?= htmlspecialchars($resena['nombre_producto']) ?></p>
          <p class="review-author">— <?= htmlspecialchars($resena['nombre_usuario'] ?: 'Cliente') ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="stats-bar">
      <div class="stat-item">
        <div class="stat-value">100%</div>
        <div class="stat-label">Ingredientes frescos</div>
      </div>
      <div class="stat-item">
        <div class="stat-value">4.8⭐</div>
        <div class="stat-label">Valoración media</div>
      </div>
      <div class="stat-item">
        <div class="stat-value">24h</div>
        <div class="stat-label">Pedidos rápidos</div>
      </div>
    </div>

    <footer>
      <p style="text-align: center; color: #718096;">Calle Falsa 123, Barcelona</p>
      <p style="text-align: center; color: #718096;">contacto@zyma.com | +34 600 000 000</p>
      <p class="social-links" style="text-align: center;">
        <a href="#">Facebook</a> ·
        <a href="#">Instagram</a> ·
        <a href="#">Twitter</a>
      </p>
      <p data-i18n="footer.rights" style="text-align: center;">&copy; 2025 Zyma. Todos los derechos reservados.</p>
      <p class="footer-legal-links" style="text-align: center;">
        <a href="politica_cookies.php">Política de Cookies</a>
        <span>|</span>
        <a href="politica_privacidad.php">Política de Privacidad</a>
        <span>|</span>
        <a href="aviso_legal.php">Aviso Legal</a>
      </p>
    </footer>
  </div>

  <script>
    const profileBtn = document.getElementById('profileBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');
    const quickDropdown = document.getElementById('quickDropdown');
    
    if (profileBtn && dropdownMenu) {
      profileBtn.addEventListener('click', () => dropdownMenu.classList.toggle('show'));
      window.addEventListener('click', (e) => {
        if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
          dropdownMenu.classList.remove('show');
        }
      });
    }
    
    if (quickDropdown) {
      const quickMenuBtn = document.getElementById('quickMenuBtn');
      if (quickMenuBtn) {
        quickMenuBtn.addEventListener('click', () => quickDropdown.classList.toggle('show'));
      }
    }
  </script>
  <script src="<?= $basePath ?>/assets/mobile-header.js?v=20260211-6"></script>
  <script src="<?= $basePath ?>/assets/lang.js?v=20260428-1"></script>
</body>
</html>

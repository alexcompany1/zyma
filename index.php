<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
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
  <link rel="stylesheet" href="<?= $basePath ?>/styles.css?v=20260211-5">
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
</body>
</html>

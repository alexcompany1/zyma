<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Política de Cookies - Zyma</title>
  <link rel="stylesheet" href="styles.css?v=20260317-1">
</head>
<body>
  <div class="container legal-page-wrapper">
    <header class="landing-header">
      <div class="landing-bar">
        <a href="<?= $is_logged_in ? 'usuario.php' : 'index.php' ?>" class="landing-logo">
          <span class="landing-logo-text">Zyma</span>
        </a>
        <div class="landing-actions">
          <?php if (!$is_logged_in): ?>
            <a href="login.php" class="landing-link">Entrar</a>
            <a href="registro.php" class="landing-cta">Crear cuenta</a>
          <?php else: ?>
            <a href="usuario.php" class="landing-link">Inicio</a>
            <a href="logout.php" class="landing-cta">Cerrar Sesión</a>
          <?php endif; ?>
        </div>
      </div>
    </header>

    <main class="legal-main-card">
      <section class="legal-hero">
        <h1>Política de Cookies</h1>
        <p>Esta web usa cookies para ofrecer una experiencia más rápida, útil y personalizada para nuestros clientes.</p>
      </section>

      <section class="legal-section">
        <h2>1. Que son las cookies</h2>
        <p>Son pequeños archivos que se guardan en tu dispositivo para recordar preferencias y mejorar la navegación.</p>
      </section>

      <section class="legal-section">
        <h2>2. Tipos de cookies que usamos</h2>
        <p>Cookies técnicas (necesarias), analíticas (medición de uso) y de marketing (contenido promocional personalizado).</p>
      </section>

      <section class="legal-section">
        <h2>3. Finalidades</h2>
        <p>Permiten mantener tu Sesión, recordar configuraciones, analizar comportamiento de uso y optimizar el rendimiento del sitio.</p>
      </section>

      <section class="legal-section">
        <h2>4. Gestion del consentimiento</h2>
        <p>Puedes aceptar, rechazar o personalizar las cookies opcionales desde el popup al iniciar Sesión.</p>
      </section>

      <section class="legal-section">
        <h2>5. Como desactivar cookies</h2>
        <p>Tambien puedes borrar o bloquear cookies desde la configuración de tu navegador en cualquier momento.</p>
      </section>

      <section class="legal-section">
        <h2>6. Actualizaciones</h2>
        <p>Podemos actualizar esta política para reflejar cambios legales o técnicos. Publicaremos siempre la versión vigente en esta página.</p>
      </section>
    </main>

    <footer>
      <p>&copy; 2026 Zyma. Todos los derechos reservados.</p>
      <p class="footer-legal-links">
        <a href="politica_cookies.php">Política de Cookies</a>
        <span>|</span>
        <a href="politica_privacidad.php">Política de Privacidad</a>
        <span>|</span>
        <a href="aviso_legal.php">Aviso Legal</a>
      </p>
    </footer>
  </div>
  <script src="assets/mobile-header.js?v=20260211-6"></script>
</body>
</html>


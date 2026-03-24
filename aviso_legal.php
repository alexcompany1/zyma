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
  <title>Aviso Legal - Zyma</title>
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
        <h1>Aviso Legal</h1>
        <p>Información legal sobre el uso de la web de Zyma y las condiciones de acceso a nuestros servicios digitales.</p>
      </section>

      <section class="legal-section">
        <h2>1. Titular del sitio</h2>
        <p>Zyma Restauracion S.L., con domicilio en Barcelona, España, es titular del sitio web y de su contenido.</p>
      </section>

      <section class="legal-section">
        <h2>2. Condiciones de uso</h2>
        <p>El acceso y uso de esta web implica la aceptación de este aviso legal y del resto de políticas publicadas.</p>
      </section>

      <section class="legal-section">
        <h2>3. Propiedad intelectual</h2>
        <p>Textos, imagenes, marca y diseño pertenecen a Zyma o a sus titulares legitimados. Queda prohibida su reproduccion no autorizada.</p>
      </section>

      <section class="legal-section">
        <h2>4. Responsabilidad</h2>
        <p>Zyma no se responsabiliza de interrupciones ajenas a su control ni del uso indebido del contenido por parte de terceros.</p>
      </section>

      <section class="legal-section">
        <h2>5. Enlaces externos</h2>
        <p>La web puede contener enlaces a terceros. Zyma no controla ni asume responsabilidad sobre su contenido o políticas.</p>
      </section>

      <section class="legal-section">
        <h2>6. Legislacion aplicable</h2>
        <p>Estas condiciones se rigen por la legislacion española y la jurisdiccion que corresponda segun normativa de consumo.</p>
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


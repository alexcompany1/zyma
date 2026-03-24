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
  <title>Política de Privacidad - Zyma</title>
  <link rel="icon" type="image/png" href="assets/favicon.png">
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
        <h1>Política de Privacidad</h1>
        <p>En Zyma cuidamos tus datos igual que cuidamos nuestros ingredientes: con transparencia y responsabilidad.</p>
      </section>

      <section class="legal-section">
        <h2>1. Responsable del tratamiento</h2>
        <p>Zyma Restauracion S.L. es responsable del tratamiento de tus datos personales en esta web y en la gestion de pedidos.</p>
      </section>

      <section class="legal-section">
        <h2>2. Datos que recopilamos</h2>
        <p>Podemos tratar datos de identificacion y contacto, datos de cuenta, historial de pedidos y comunicaciones de soporte o valoraciones.</p>
      </section>

      <section class="legal-section">
        <h2>3. Finalidades del tratamiento</h2>
        <p>Usamos tus datos para gestionar tu registro, tramitar pedidos, atender consultas, mejorar el servicio y cumplir obligaciones legales.</p>
      </section>

      <section class="legal-section">
        <h2>4. Base jurídica</h2>
        <p>Tratamos los datos por ejecucion de contrato, consentimiento del usuario y cumplimiento de obligaciones legales aplicables.</p>
      </section>

      <section class="legal-section">
        <h2>5. Conservacion de datos</h2>
        <p>Conservamos la información durante el tiempo necesario para la finalidad indicada y los plazos legales exigibles.</p>
      </section>

      <section class="legal-section">
        <h2>6. Destinatarios</h2>
        <p>No cedemos datos a terceros salvo obligacion legal o proveedores necesarios para operar la plataforma bajo contrato de confidencialidad.</p>
      </section>

      <section class="legal-section">
        <h2>7. Tus derechos</h2>
        <p>Puedes solicitar acceso, rectificacion, supresion, oposicion, limitacion y portabilidad de tus datos escribiendo a privacidad@zyma.com.</p>
      </section>

      <section class="legal-section">
        <h2>8. Seguridad</h2>
        <p>Aplicamos medidas técnicas y organizativas para proteger la información y evitar accesos no autorizados.</p>
      </section>

      <section class="legal-section">
        <h2>9. Contacto de privacidad</h2>
        <p>Si tienes dudas sobre esta política, puedes contactar con nuestro equipo en privacidad@zyma.com.</p>
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


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

$mensaje = '';
$error = '';

$user = null;
try {
    $stmt = $pdo->prepare('SELECT id, nombre, email, worker_code FROM usuarios WHERE id = :id');
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = 'Error al leer los datos de usuario.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $nombre = trim($_POST['nombre'] ?? '');

        if ($nombre === '') {
            $error = 'El nombre es obligatorio.';
        } elseif (strlen($nombre) > 60) {
            $error = 'El nombre no puede superar 60 caracteres.';
        } else {
            try {
                $stmt = $pdo->prepare('UPDATE usuarios SET nombre = :nombre WHERE id = :id');
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':id' => $_SESSION['user_id'],
                ]);

                $_SESSION['nombre'] = $nombre;
                if (is_array($user)) {
                    $user['nombre'] = $nombre;
                }
                $mensaje = 'Perfil actualizado correctamente.';
            } catch (Exception $e) {
                $error = 'Error al guardar los cambios.';
            }
        }
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $error = 'Todos los campos de contraseña son obligatorios.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'La nueva contraseña debe tener al menos 8 caracteres.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'La nueva contraseña y su confirmación no coinciden.';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT password_hash FROM usuarios WHERE id = :id');
                $stmt->execute([':id' => $_SESSION['user_id']]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
                    $error = 'Contraseña actual incorrecta.';
                } else {
                    $updatedHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $update = $pdo->prepare('UPDATE usuarios SET password_hash = :hash WHERE id = :id');
                    $update->execute([
                        ':hash' => $updatedHash,
                        ':id' => $_SESSION['user_id'],
                    ]);
                    $mensaje = 'Contraseña actualizada correctamente.';
                }
            } catch (Exception $e) {
                $error = 'Error al actualizar la contraseña.';
            }
        }
    }
}

$display_name = trim($_SESSION['nombre'] ?? '');
if ($display_name === '') {
    $display_name = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? '');
}

$profileName = trim($user['nombre'] ?? $_SESSION['nombre'] ?? '');
$profileEmail = trim($user['email'] ?? $_SESSION['email'] ?? '');
$profileRole = !empty($user['worker_code']) ? strtoupper((string) $user['worker_code']) : 'CLIENTE';

$nameParts = preg_split('/\s+/', $profileName, -1, PREG_SPLIT_NO_EMPTY);
$initials = '';
if (!empty($nameParts)) {
    $initials .= strtoupper(substr($nameParts[0], 0, 1));
    if (count($nameParts) > 1) {
        $initials .= strtoupper(substr($nameParts[count($nameParts) - 1], 0, 1));
    }
}
if ($initials === '') {
    $initials = strtoupper(substr($profileEmail !== '' ? $profileEmail : $display_name, 0, 2));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zyma - Mi Perfil</title>
  <link rel="icon" type="image/png" href="assets/favicon.png">
  <link rel="stylesheet" href="styles.css?v=20260320-1">
</head>
<body>
<header class="landing-header">
  <div class="landing-bar">
    <div class="profile-section">
      <button class="profile-btn" id="profileBtn">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="white">
          <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c1.52 0 5.1 1.34 5.1 5v1H6.9v-1c0-3.66 3.58-5 5.1-5z"/>
        </svg>
      </button>
      <span class="user-name"><?= htmlspecialchars($display_name) ?></span>
      <div class="dropdown" id="dropdownMenu">
        <a href="perfil.php" data-i18n="nav.myProfile">Mi perfil</a>
        <a href="politica_cookies.php" class="open-cookie-preferences" data-i18n="nav.customizeCookies">Personalizar cookies</a>
        <a href="logout.php" data-i18n="nav.logout">Cerrar sesión</a>
      </div>
    </div>

    <a href="usuario.php" class="landing-logo">
      <span class="landing-logo-text">Zyma</span>
    </a>

    <div class="quick-menu-section">
      <button class="quick-menu-btn" id="quickMenuBtn" aria-label="Menú rápido"></button>
      <div class="dropdown quick-dropdown" id="quickDropdown">
        <a href="usuario.php" data-i18n="nav.home">Inicio</a>
        <a href="carta.php" data-i18n="nav.viewMenu">Ver carta</a>
        <a href="valoraciones.php" data-i18n="nav.reviews">Valoraciones</a>
        <a href="tickets.php" data-i18n="nav.tickets">Tickets</a>
      </div>
    </div>
    <div class="cart-section">
      <a href="carrito.php" class="cart-btn">
        <img src="assets/cart-icon.png" alt="Carrito">
        <span class="cart-count"><?= count($_SESSION['cart'] ?? []) ?></span>
      </a>
    </div>
  </div>
</header>

<main class="container profile-page">
  <?php if ($mensaje): ?>
    <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <section class="profile-hero">
    <div class="profile-hero-main">
      <div class="profile-avatar" aria-hidden="true"><?= htmlspecialchars($initials) ?></div>
      <div class="profile-hero-copy">
        <span class="profile-eyebrow" data-i18n="profile.personalPanel">Panel personal</span>
        <h1><?= htmlspecialchars($profileName !== '' ? $profileName : $display_name) ?></h1>
        <p data-i18n="profile.description">Gestiona tu cuenta desde un espacio más claro, profesional y fácil de usar.</p>
      </div>
    </div>
    <div class="profile-badges">
      <span class="profile-badge" data-i18n="profile.activeAccount">Cuenta activa</span>
      <span class="profile-badge"><?= htmlspecialchars($profileRole) ?></span>
      <span class="profile-badge" data-i18n="profile.strongSecurity">Seguridad reforzada</span>
    </div>
  </section>

  <div class="profile-grid profile-grid-pro">
    <aside class="profile-card profile-summary-card">
      <div class="profile-card-header">
        <span class="profile-section-kicker" data-i18n="profile.summaryKicker">Resumen</span>
        <h2 data-i18n="profile.accountInfo">Información de cuenta</h2>
      </div>

      <dl class="profile-data-list">
        <div class="profile-data-row">
          <dt data-i18n="profile.name">Nombre</dt>
          <dd><?= htmlspecialchars($profileName) ?></dd>
        </div>
        <div class="profile-data-row">
          <dt data-i18n="profile.email">Email</dt>
          <dd><?= htmlspecialchars($profileEmail) ?></dd>
        </div>
        <div class="profile-data-row">
          <dt data-i18n="profile.role">Rol</dt>
          <dd><?= htmlspecialchars($profileRole) ?></dd>
        </div>
        <div class="profile-data-row">
          <dt data-i18n="profile.status">Estado</dt>
          <dd data-i18n="profile.operative">Perfil operativo</dd>
        </div>
      </dl>

      <div class="profile-highlight-box">
        <strong data-i18n="profile.proTipTitle">Consejo profesional</strong>
        <p data-i18n="profile.proTipBody">Usa tu nombre completo y revisa tu contraseña con frecuencia para mantener una imagen más cuidada y segura.</p>
      </div>

      <a class="profile-secondary-link" href="usuario.php" data-i18n="profile.backToPanel">Volver al panel principal</a>
    </aside>

    <div class="profile-stack">
      <section class="profile-card profile-form-card">
        <div class="profile-card-header">
          <span class="profile-section-kicker" data-i18n="profile.personalDataKicker">Datos personales</span>
          <h2 data-i18n="profile.editProfile">Editar perfil</h2>
          <p data-i18n="profile.editDesc">Actualiza la información visible de tu cuenta para dar una imagen más profesional.</p>
        </div>

        <form method="POST" action="perfil.php" class="profile-form">
          <input type="hidden" name="action" value="update_profile">

          <label for="nombre">
            <span data-i18n="profile.fullName">Nombre completo</span> <span class="required">*</span>
            <input
              type="text"
              id="nombre"
              name="nombre"
              required
              pattern=".*\S.*"
              title="Introduce un nombre valido"
              value="<?= htmlspecialchars($profileName) ?>"
              placeholder="Escribe tu nombre completo"
              data-i18n-placeholder="profile.fullName"
            >
          </label>

          <p class="profile-field-note" data-i18n="profile.nameNote">Este nombre se mostrará en tu área privada y ayuda a que el perfil se vea más serio y ordenado.</p>

          <button type="submit" data-i18n="common.saveChanges">Guardar cambios</button>
        </form>
      </section>

      <section class="profile-card profile-form-card">
        <div class="profile-card-header">
          <span class="profile-section-kicker" data-i18n="profile.securityKicker">Seguridad</span>
          <h2 data-i18n="profile.changePassword">Cambiar contraseña</h2>
          <p data-i18n="profile.securityDesc">Actualiza tu clave para mantener protegido el acceso a la cuenta.</p>
        </div>

        <form method="POST" action="perfil.php" class="profile-form">
          <input type="hidden" name="action" value="change_password">

          <label for="current_password">
            <span data-i18n="profile.currentPassword">Contraseña actual</span> <span class="required">*</span>
            <div class="password-field">
              <input type="password" id="current_password" name="current_password" required minlength="6" placeholder="Introduce tu contraseña actual">
              <button type="button" class="password-toggle" data-password-toggle="current_password" data-i18n-aria="common.showPassword" aria-label="Mostrar contraseña" aria-pressed="false">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
              </button>
            </div>
          </label>

          <div class="profile-form-split">
            <label for="new_password">
              <span data-i18n="profile.newPassword">Nueva contraseña</span> <span class="required">*</span>
              <div class="password-field">
                <input type="password" id="new_password" name="new_password" required minlength="8" placeholder="Mínimo 8 caracteres">
                <button type="button" class="password-toggle" data-password-toggle="new_password" data-i18n-aria="common.showPassword" aria-label="Mostrar contraseña" aria-pressed="false">
                  <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                </button>
              </div>
            </label>

            <label for="confirm_password">
              <span data-i18n="profile.confirmNewPwd">Confirmar nueva contraseña</span> <span class="required">*</span>
              <div class="password-field">
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8" placeholder="Repite la nueva contraseña">
                <button type="button" class="password-toggle" data-password-toggle="confirm_password" data-i18n-aria="common.showPassword" aria-label="Mostrar contraseña" aria-pressed="false">
                  <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                </button>
              </div>
            </label>
          </div>

          <p class="profile-field-note" data-i18n="profile.passwordNote">Combina letras, números y símbolos para conseguir una clave más fuerte.</p>

          <button type="submit" data-i18n="profile.updatePassword">Actualizar contraseña</button>
        </form>
      </section>
    </div>
  </div>
</main>

<script>
const profileBtn = document.getElementById('profileBtn');
const dropdownMenu = document.getElementById('dropdownMenu');
if (profileBtn && dropdownMenu) {
  profileBtn.addEventListener('click', () => {
    dropdownMenu.classList.toggle('show');
  });

  window.addEventListener('click', (e) => {
    if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
      dropdownMenu.classList.remove('show');
    }
  });
}
</script>
<script src="assets/mobile-header.js?v=20260211-6"></script>
<script src="assets/lang.js?v=1"></script>
<script>
document.querySelectorAll('[data-password-toggle]').forEach((button) => {
  button.addEventListener('click', () => {
    const input = document.getElementById(button.dataset.passwordToggle);
    if (!input) return;

    const showPassword = input.type === 'password';
    input.type = showPassword ? 'text' : 'password';
    button.setAttribute('aria-label', showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
    button.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
  });
});
</script>
<footer>
  <p>&copy; 2025 Zyma. Todos los derechos reservados.</p>
  <p class="footer-legal-links">
    <a href="politica_cookies.php" data-i18n="footer.cookiePolicy">Política de Cookies</a>
    <span>|</span>
    <a href="politica_privacidad.php" data-i18n="footer.privacy">Política de Privacidad</a>
    <span>|</span>
    <a href="aviso_legal.php" data-i18n="footer.legal">Aviso Legal</a>
  </p>
</footer>
</body>
</html>

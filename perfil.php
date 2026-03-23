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
            $error = 'Todos los campos de contrasena son obligatorios.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'La nueva contrasena debe tener al menos 8 caracteres.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'La nueva contrasena y su confirmacion no coinciden.';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT password_hash FROM usuarios WHERE id = :id');
                $stmt->execute([':id' => $_SESSION['user_id']]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
                    $error = 'Contrasena actual incorrecta.';
                } else {
                    $updatedHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $update = $pdo->prepare('UPDATE usuarios SET password_hash = :hash WHERE id = :id');
                    $update->execute([
                        ':hash' => $updatedHash,
                        ':id' => $_SESSION['user_id'],
                    ]);
                    $mensaje = 'Contrasena actualizada correctamente.';
                }
            } catch (Exception $e) {
                $error = 'Error al actualizar la contrasena.';
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
        <span class="profile-eyebrow">Panel personal</span>
        <h1><?= htmlspecialchars($profileName !== '' ? $profileName : $display_name) ?></h1>
        <p>Gestiona tu cuenta desde un espacio mas claro, profesional y facil de usar.</p>
      </div>
    </div>
    <div class="profile-badges">
      <span class="profile-badge">Cuenta activa</span>
      <span class="profile-badge"><?= htmlspecialchars($profileRole) ?></span>
      <span class="profile-badge">Seguridad reforzada</span>
    </div>
  </section>

  <div class="profile-grid profile-grid-pro">
    <aside class="profile-card profile-summary-card">
      <div class="profile-card-header">
        <span class="profile-section-kicker">Resumen</span>
        <h2>Informacion de cuenta</h2>
      </div>

      <dl class="profile-data-list">
        <div class="profile-data-row">
          <dt>Nombre</dt>
          <dd><?= htmlspecialchars($profileName) ?></dd>
        </div>
        <div class="profile-data-row">
          <dt>Email</dt>
          <dd><?= htmlspecialchars($profileEmail) ?></dd>
        </div>
        <div class="profile-data-row">
          <dt>Rol</dt>
          <dd><?= htmlspecialchars($profileRole) ?></dd>
        </div>
        <div class="profile-data-row">
          <dt>Estado</dt>
          <dd>Perfil operativo</dd>
        </div>
      </dl>

      <div class="profile-highlight-box">
        <strong>Consejo profesional</strong>
        <p>Usa tu nombre completo y revisa tu contrasena con frecuencia para mantener una imagen mas cuidada y segura.</p>
      </div>

      <a class="profile-secondary-link" href="usuario.php">Volver al panel principal</a>
    </aside>

    <div class="profile-stack">
      <section class="profile-card profile-form-card">
        <div class="profile-card-header">
          <span class="profile-section-kicker">Datos personales</span>
          <h2>Editar perfil</h2>
          <p>Actualiza la informacion visible de tu cuenta para dar una imagen mas profesional.</p>
        </div>

        <form method="POST" action="perfil.php" class="profile-form">
          <input type="hidden" name="action" value="update_profile">

          <label for="nombre">
            Nombre completo <span class="required">*</span>
            <input
              type="text"
              id="nombre"
              name="nombre"
              required
              pattern=".*\S.*"
              title="Introduce un nombre valido"
              value="<?= htmlspecialchars($profileName) ?>"
              placeholder="Escribe tu nombre completo"
            >
          </label>

          <p class="profile-field-note">Este nombre se mostrara en tu area privada y ayuda a que el perfil se vea mas serio y ordenado.</p>

          <button type="submit">Guardar cambios</button>
        </form>
      </section>

      <section class="profile-card profile-form-card">
        <div class="profile-card-header">
          <span class="profile-section-kicker">Seguridad</span>
          <h2>Cambiar contrasena</h2>
          <p>Actualiza tu clave para mantener protegido el acceso a la cuenta.</p>
        </div>

        <form method="POST" action="perfil.php" class="profile-form">
          <input type="hidden" name="action" value="change_password">

          <label for="current_password">
            Contrasena actual <span class="required">*</span>
            <input type="password" id="current_password" name="current_password" required minlength="6" placeholder="Introduce tu contrasena actual">
          </label>

          <div class="profile-form-split">
            <label for="new_password">
              Nueva contrasena <span class="required">*</span>
              <input type="password" id="new_password" name="new_password" required minlength="8" placeholder="Minimo 8 caracteres">
            </label>

            <label for="confirm_password">
              Confirmar nueva contrasena <span class="required">*</span>
              <input type="password" id="confirm_password" name="confirm_password" required minlength="8" placeholder="Repite la nueva contrasena">
            </label>
          </div>

          <p class="profile-field-note">Combina letras, numeros y simbolos para conseguir una clave mas fuerte.</p>

          <button type="submit">Actualizar contrasena</button>
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
</body>
</html>

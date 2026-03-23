<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
?>
<?php
/**
 * perfil.php
 * Editar nombre del usuario.
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if ($nombre === '') {
        $error = 'El nombre es obligatorio.';
    } elseif (strlen($nombre) > 60) {
        $error = 'El nombre no puede superar 60 caracteres.';
    } elseif (($password !== '' || $confirmPassword !== '') && strlen($password) < 6) {
        $error = 'La contrasena debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Las contrasenas no coinciden.';
    } else {
        try {
            if ($password !== '') {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = :nombre, password_hash = :password_hash WHERE id = :id");
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':password_hash' => $passwordHash,
                    ':id' => $_SESSION['user_id']
                ]);
                $mensaje = 'Perfil y contrasena actualizados correctamente.';
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = :nombre WHERE id = :id");
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':id' => $_SESSION['user_id']
                ]);
                $mensaje = 'Nombre actualizado correctamente.';
            }

            $_SESSION['nombre'] = $nombre;
        } catch (Exception $e) {
            $error = 'Error al guardar los cambios.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zyma - Mi Perfil</title>
  <link rel="stylesheet" href="styles.css?v=20260211-5">
</head>
<body>
<?php
  $display_name = trim($_SESSION['nombre'] ?? '');
  if ($display_name === '') {
    $display_name = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? '');
  }
?>
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

<div class="container">
  <?php if ($mensaje): ?>
    <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="perfil.php">
    <h2>Mi perfil</h2>

    <label for="nombre">
      Nombre <span class="required">*</span>
      <input type="text" id="nombre" name="nombre" required pattern=".*\S.*" title="Introduce un nombre válido"
             value="<?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>">
    </label>

    <label for="email">
      Email
      <input type="email" id="email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" disabled>
    </label>

    <label for="password">
      Nueva contrasena
      <div class="password-field">
        <input type="password" id="password" name="password" minlength="6">
        <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Mostrar contrasena" aria-pressed="false">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
        </button>
      </div>
      <span class="optional-label">Dejalo vacio si no quieres cambiarla.</span>
    </label>

    <label for="confirmPassword">
      Confirmar nueva contrasena
      <div class="password-field">
        <input type="password" id="confirmPassword" name="confirmPassword" minlength="6">
        <button type="button" class="password-toggle" data-password-toggle="confirmPassword" aria-label="Mostrar contrasena" aria-pressed="false">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
        </button>
      </div>
    </label>

    <button type="submit">Guardar cambios</button>
  </form>
</div>

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
<script>
document.querySelectorAll('[data-password-toggle]').forEach((button) => {
  button.addEventListener('click', () => {
    const input = document.getElementById(button.dataset.passwordToggle);
    if (!input) return;

    const showPassword = input.type === 'password';
    input.type = showPassword ? 'text' : 'password';
    button.setAttribute('aria-label', showPassword ? 'Ocultar contrasena' : 'Mostrar contrasena');
    button.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
  });
});
</script>
</body>
</html>

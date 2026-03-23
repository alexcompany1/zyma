<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

/**
 * login.php
 * Inicio de sesion.
 */

session_start();
require_once 'config.php';

function usuariosTieneBloqueado(PDO $pdo): bool
{
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'bloqueado'");
    return (int) $stmt->fetchColumn() > 0;
}

$error = '';
$info = '';
$supportsBloqueado = usuariosTieneBloqueado($pdo);

if (isset($_GET['reset']) && $_GET['reset'] === '1') {
    $info = 'Tu contrasena se ha actualizado. Ya puedes iniciar sesion.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $workerCode = trim($_POST['workerCode'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'El email y la contrasena son obligatorios.';
    } else {
        try {
            $sql = "SELECT id, nombre, email, password_hash, worker_code" . ($supportsBloqueado ? ", bloqueado" : ", 0 AS bloqueado") . " FROM usuarios WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $credencialesOk = $user && password_verify($password, $user['password_hash']);

            if ($credencialesOk && (int) ($user['bloqueado'] ?? 0) === 1) {
                $error = 'Tu cuenta esta bloqueada. Contacta con administracion.';
            } elseif ($credencialesOk) {
                if (!empty($workerCode)) {
                    if (!empty($user['worker_code']) && hash_equals($user['worker_code'], $workerCode)) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['nombre'] = $user['nombre'] ?? '';
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['worker_code'] = $user['worker_code'];
                        if ($user['worker_code'] === 'ADMIN') {
                            header('Location: admin.php');
                            exit;
                        }
                        header('Location: trabajador.php');
                        exit;
                    }
                    $error = 'Codigo de trabajador incorrecto.';
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nombre'] = $user['nombre'] ?? '';
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['worker_code'] = $user['worker_code'];
                    header('Location: usuario.php');
                    exit;
                }
            } else {
                $error = 'Credenciales incorrectas.';
            }
        } catch (Exception $e) {
            $error = 'Error interno del sistema.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar sesion - Zyma</title>
  <link rel="stylesheet" href="styles.css?v=20260211-5">
</head>
<body>
  <div class="container">
    <header class="landing-header">
      <div class="landing-bar">
        <a href="index.php" class="landing-logo">
          <span class="landing-logo-text">Zyma</span>
        </a>
        <div class="landing-actions">
          <a href="registro.php" class="landing-cta">Crear cuenta</a>
        </div>
      </div>
    </header>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($info): ?>
      <div class="alert"><?= htmlspecialchars($info) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <h2>Iniciar sesion</h2>

      <label for="email">
        Email <span class="required">*</span>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </label>

      <label for="password">
        Contrasena <span class="required">*</span>
        <div class="password-field">
          <input type="password" id="password" name="password" required>
          <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Mostrar contrasena" aria-pressed="false">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
          </button>
        </div>
      </label>

      <label for="workerCode">
        Codigo de trabajador (opcional)
        <input type="text" id="workerCode" name="workerCode" value="<?= htmlspecialchars($_POST['workerCode'] ?? '') ?>">
        <span class="optional-label">Trabajador: ej. TRAB001<br>Administrador: ADMIN</span>
      </label>

      <button type="submit">Iniciar sesion</button>
    </form>

    <div class="center mt-3">
      <a href="registro.php">No tienes cuenta? Registrate</a>
    </div>
    <div class="center mt-2">
      <a href="forgot_password.php">He olvidado la contrasena</a>
    </div>
  </div>
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

<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

/**
 * login.php
 * Inicio de SesiÃ³n.
 */

session_start();
require_once 'config.php';
require_once 'cookie_consent_helper.php';

function usuariosTieneBloqueado(PDO $pdo): bool
{
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'bloqueado'");
    return (int) $stmt->fetchColumn() > 0;
}

function prepararConsentimientoCookiesSesion(PDO $pdo, int $userId): void
{
    $consent = getCookieConsentByUser($pdo, $userId);
    $_SESSION['cookie_preferences'] = [
        'analytics' => (int)($consent['analytics'] ?? 0) === 1,
        'marketing' => (int)($consent['marketing'] ?? 0) === 1,
        'policy_version' => $consent['policy_version'] ?? null,
        'estado' => $consent['estado'] ?? null,
    ];
    $_SESSION['show_cookie_popup'] = shouldShowCookiePopup($pdo, $userId);
}

$error = '';
$info = '';
$supportsBloqueado = usuariosTieneBloqueado($pdo);

if (isset($_GET['reset']) && $_GET['reset'] === '1') {
    $info = 'Tu ContraseÃ±a se ha actualizado. Ya puedes iniciar SesiÃ³n.';
}
if (isset($_GET['cookie_rejected']) && $_GET['cookie_rejected'] === '1') {
    $info = 'Para usar tu cuenta en Zyma necesitamos aceptar las cookies tÃ©cnicas. Puedes volver a iniciar sesiÃ³n y configurar tus preferencias.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $workerCode = trim($_POST['workerCode'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'El email y la ContraseÃ±a son obligatorios.';
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
                        prepararConsentimientoCookiesSesion($pdo, (int)$user['id']);
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
                    prepararConsentimientoCookiesSesion($pdo, (int)$user['id']);
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
  <title>Iniciar SesiÃ³n - Zyma</title>
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
      <h2>Iniciar SesiÃ³n</h2>

      <label for="email">
        Email <span class="required">*</span>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </label>

      <label for="password">
        ContraseÃ±a <span class="required">*</span>
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

      <button type="submit">Iniciar SesiÃ³n</button>
    </form>

    <div class="center mt-3">
      <a href="registro.php">No tienes cuenta? Registrate</a>
    </div>
    <div class="center mt-2">
      <a href="forgot_password.php">He olvidado la ContraseÃ±a</a>
    </div>
    <div class="center mt-3 footer-legal-links">
      <a href="politica_cookies.php">PolÃ­tica de Cookies</a>
      <span>|</span>
      <a href="politica_privacidad.php">PolÃ­tica de Privacidad</a>
      <span>|</span>
      <a href="aviso_legal.php">Aviso Legal</a>
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

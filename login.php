<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
?>
<?php
/**
 * login.php
 * Inicio de sesion.
 */

session_start();
require_once 'config.php';

function usuariosTieneBloqueado(PDO $pdo): bool
{
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'usuarios'
          AND COLUMN_NAME = 'bloqueado'
    ");
    return (int)$stmt->fetchColumn() > 0;
}

$error = '';
$supportsBloqueado = usuariosTieneBloqueado($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $workerCode = trim($_POST['workerCode'] ?? '');

    if (empty($email) || empty($password)) {
        $error = "El email y la contraseña son obligatorios.";
    } else {
        try {
            $sql = "SELECT id, nombre, email, password_hash, worker_code" . ($supportsBloqueado ? ", bloqueado" : ", 0 AS bloqueado") . " FROM usuarios WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $credencialesOk = $user && password_verify($password, $user['password_hash']);

            if ($credencialesOk && (int)($user['bloqueado'] ?? 0) === 1) {
                $error = "Tu cuenta esta bloqueada. Contacta con administracion.";
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
                        } else {
                            header('Location: trabajador.php');
                            exit;
                        }
                    } else {
                        $error = "Código de trabajador incorrecto.";
                    }
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nombre'] = $user['nombre'] ?? '';
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['worker_code'] = $user['worker_code'];
                    header('Location: usuario.php');
                    exit;
                }
            } elseif ($error === '') {
                $error = "Credenciales incorrectas.";
            }
        } catch (Exception $e) {
            $error = "Error interno del sistema.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión - Zyma</title>
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

    <form method="POST" action="login.php">
      <h2>Iniciar Sesión</h2>

      <label for="email">
        Email <span class="required">*</span>
        <input type="email" id="email" name="email" required 
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </label>

      <label for="password">
        Contraseña <span class="required">*</span>
        <input type="password" id="password" name="password" required>
      </label>

      <label for="workerCode">
        Código de trabajador (opcional)
        <input type="text" id="workerCode" name="workerCode" 
               value="<?= htmlspecialchars($_POST['workerCode'] ?? '') ?>">
        <span class="optional-label">
          Trabajador: ej. TRAB001<br>
          Administrador: ADMIN
        </span>
      </label>

      <button type="submit">Iniciar Sesión</button>
    </form>

    <div class="center mt-3">
      <a href="registro.php">¿No tienes cuenta? Regístrate</a>
    </div>
  </div>
<script src="assets/mobile-header.js?v=20260211-6"></script>
</body>
</html>

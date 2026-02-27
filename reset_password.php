<?php
/**
 * reset_password.php
 * Establece nueva contrasena con token. :)
 */

if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

session_start();
require_once 'config.php';

$error = '';
$token = trim($_GET['token'] ?? $_POST['token'] ?? '');

if ($token === '') {
    $error = 'El enlace de recuperacion no es valido.';
}

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token_hash CHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_token_hash (token_hash)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (Exception $e) {
    $error = 'No se pudo validar el enlace de recuperacion.';
}

$resetRow = null;
if ($error === '') {
    $tokenHash = hash('sha256', $token);
    $stmt = $pdo->prepare('SELECT id, user_id, expires_at, used_at FROM password_resets WHERE token_hash = :token_hash ORDER BY id DESC LIMIT 1');
    $stmt->execute(['token_hash' => $tokenHash]);
    $resetRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resetRow) {
        $error = 'El enlace de recuperacion no es valido.';
    } elseif (!empty($resetRow['used_at'])) {
        $error = 'Este enlace ya fue utilizado.';
    } elseif (strtotime($resetRow['expires_at']) < time()) {
        $error = 'El enlace ha caducado. Solicita uno nuevo.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '') {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if (strlen($password) < 6) {
        $error = 'La contrasena debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Las contrasenas no coinciden.';
    } else {
        try {
            $pdo->beginTransaction();

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $updateUser = $pdo->prepare('UPDATE usuarios SET password_hash = :password_hash WHERE id = :id');
            $updateUser->execute([
                'password_hash' => $passwordHash,
                'id' => $resetRow['user_id']
            ]);

            $markUsed = $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
            $markUsed->execute(['id' => $resetRow['id']]);

            $pdo->commit();
            header('Location: login.php?reset=1');
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'No se pudo actualizar la contrasena. Intentalo de nuevo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nueva contrasena - Zyma</title>
  <link rel="stylesheet" href="styles.css?v=20260211-5">
</head>
<body>
  <div class="container">
    <header class="landing-header">
      <div class="landing-bar">
        <a href="index.php" class="landing-logo">
          <span class="landing-logo-text">Zyma</span>
        </a>
      </div>
    </header>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <div class="center mt-3"><a href="forgot_password.php">Solicitar nuevo enlace</a></div>
    <?php else: ?>
      <form method="POST" action="reset_password.php">
        <h2>Establecer nueva contrasena</h2>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <label for="password">
          Nueva contrasena <span class="required">*</span>
          <input type="password" id="password" name="password" required minlength="6">
        </label>

        <label for="confirmPassword">
          Confirmar contrasena <span class="required">*</span>
          <input type="password" id="confirmPassword" name="confirmPassword" required minlength="6">
        </label>

        <button type="submit">Guardar contrasena</button>
      </form>
    <?php endif; ?>

    <div class="center mt-3"><a href="login.php">Volver al login</a></div>
  </div>
</body>
</html>

<?php
/**
 * forgot_password.php
 * Solicita recuperacion de contrasena por email.
 */

if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

session_start();
require_once 'config.php';

$error = '';
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Introduce un email valido.';
    } else {
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

            $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $token);
                $expiresAt = date('Y-m-d H:i:s', time() + 3600);

                $insert = $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, :expires_at)');
                $insert->execute([
                    'user_id' => $user['id'],
                    'token_hash' => $tokenHash,
                    'expires_at' => $expiresAt
                ]);

                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
                $baseUrl = $scheme . '://' . $host . ($basePath === '' ? '' : $basePath);

                $resetLink = $baseUrl . '/reset_password.php?token=' . urlencode($token);
                $subject = 'Recuperar contrasena - Zyma';
                $body = "Hola,\n\n";
                $body .= "Hemos recibido una solicitud para restablecer tu contrasena.\n";
                $body .= "Usa este enlace (valido 1 hora):\n";
                $body .= $resetLink . "\n\n";
                $body .= "Si no fuiste tu, ignora este mensaje.\n";
                $body .= "Equipo Zyma";
                $headers = "From: no-reply@zyma.local\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                @mail($email, $subject, $body, $headers);
            }

            $info = 'Si el email existe, te hemos enviado instrucciones para recuperar la contrasena.';
        } catch (Exception $e) {
            $error = 'No se pudo procesar la solicitud. Intentalo de nuevo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar contrasena - Zyma</title>
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
    <?php endif; ?>
    <?php if ($info): ?>
      <div class="alert"><?= htmlspecialchars($info) ?></div>
    <?php endif; ?>

    <form method="POST" action="forgot_password.php">
      <h2>Recuperar contrasena</h2>
      <label for="email">
        Email <span class="required">*</span>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </label>
      <button type="submit">Enviar enlace</button>
    </form>

    <div class="center mt-3">
      <a href="login.php">Volver al login</a>
    </div>
  </div>
</body>
</html>

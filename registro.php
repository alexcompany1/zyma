<?php
/**
 * registro.php
 * Registro de usuarios.
 */

require_once 'config.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $workerCode = trim($_POST['workerCode'] ?? '');

    if (empty($email) || empty($password) || empty($confirmPassword)) {
        $error = "El email, la contraseña y su confirmación son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Formato de email inválido.";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } elseif (!hash_equals($password, $confirmPassword)) {
        $error = "Las contraseñas no coinciden.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                $error = "Este email ya está registrado.";
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (email, password_hash, worker_code) VALUES (:email, :password, :workerCode)");
                $stmt->execute([
                    'email' => $email,
                    'password' => $passwordHash,
                    'workerCode' => $workerCode ?: null
                ]);

                // Guardar email para popup
                $_SESSION['registered_email'] = $email;
                header("Location: registro.php?success=1");
                exit;
            }
        } catch (Exception $e) {
            $error = "Error al registrar. Inténtalo más tarde.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrarse - Zyma</title>
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
          <a href="login.php" class="landing-link">Entrar</a>
        </div>
      </div>
    </header>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="registro.php">
      <h2>Crea tu cuenta</h2>

      <label for="email">
        Email <span class="required">*</span>
        <input type="email" id="email" name="email" required 
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </label>

      <label for="password">
        Contraseña <span class="required">*</span>
        <input type="password" id="password" name="password" required minlength="6">
        <span class="optional-label">Mínimo 6 caracteres.</span>
      </label>

      <label for="confirm_password">
        Confirmar contraseña <span class="required">*</span>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
      </label>

      <label for="workerCode">
        Código de trabajador (opcional)
        <input type="text" id="workerCode" name="workerCode" 
               value="<?= htmlspecialchars($_POST['workerCode'] ?? '') ?>">
        <span class="optional-label">Si lo tienes, accederás a funciones especiales.</span>
      </label>

      <button type="submit">Registrarse</button>
    </form>

    <div class="center mt-3">
      <a href="login.php">¿Ya tienes cuenta? Inicia sesión</a>
    </div>
  </div>

  <?php if (isset($_GET['success']) && $_GET['success'] == 1 && isset($_SESSION['registered_email'])): ?>
  <script>
    // Mostrar popup
    document.addEventListener('DOMContentLoaded', function() {
      // Crear overlay
      const overlay = document.createElement('div');
      overlay.className = 'popup-overlay';
      
      // Crear contenedor
      const popup = document.createElement('div');
      popup.className = 'popup-card';
      
      // Contenido
      popup.innerHTML = `
        <div class="popup-icon">[OK]</div>
        <h2 class="popup-title">¡Registro exitoso!</h2>
        <p class="popup-text">
          Tu cuenta ha sido creada correctamente.<br>
          <strong><?= htmlspecialchars($_SESSION['registered_email']) ?></strong>
        </p>
        <div class="popup-box">
          <p>Redirigiendo al login en <span id="countdown">3</span> segundos...</p>
        </div>
        <button class="popup-btn" onclick="window.location.href='login.php'">
          Ir al Login ahora
        </button>
      `;
      
      // Insertar en DOM
      overlay.appendChild(popup);
      document.body.appendChild(overlay);
      
      // Cuenta atras
      let countdown = 3;
      const countdownElement = document.getElementById('countdown');
      
      const redirectTimer = setInterval(() => {
        countdown--;
        if (countdown >= 0) {
          countdownElement.textContent = countdown;
        }
        if (countdown < 0) {
          clearInterval(redirectTimer);
          window.location.href = 'login.php';
        }
      }, 1000);
      
      // Limpiar sesion
      <?php unset($_SESSION['registered_email']); ?>
    });
  </script>
  <?php endif; ?>
  <script src="assets/mobile-header.js?v=20260211-6"></script>
</body>
</html>

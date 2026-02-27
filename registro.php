<?php
/**
 * Página de registro de nuevos usuarios
 * 
 * Permite crear nuevas cuentas en el sistema.
 * Valida el formato del email y la unicidad del mismo.
 * Requiere confirmación de contraseña.
 * Muestra un popup de confirmación antes de redirigir al login.
 * 
 * @author Equipo Zyma
 * @version 1.0
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

                // Guardar email en sesión para mostrar en el popup
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

      <label for="confirmPassword">
        Confirmar contraseña <span class="required">*</span>
        <input type="password" id="confirmPassword" name="confirmPassword" required minlength="6">
        <span class="optional-label">Repite tu contraseña para confirmar.</span>
      </label>

      <label for="workerCode">
        Código de trabajador (opcional)
        <input type="text" id="workerCode" name="workerCode" 
               value="<?= htmlspecialchars($_POST['workerCode'] ?? '') ?>">
        <span class="optional-label">Si lo tienes, accederás a funciones especiales.</span>
      </label>

      <button type="submit">Registrarse</button>
    </form>

    <div style="text-align: center; margin-top: 1.8rem;">
      <a href="login.php">¿Ya tienes cuenta? Inicia sesión</a>
    </div>
  </div>

  <?php if (isset($_GET['success']) && $_GET['success'] == 1 && isset($_SESSION['registered_email'])): ?>
  <script>
    // Mostrar popup de éxito con animación
    document.addEventListener('DOMContentLoaded', function() {
      // Crear overlay oscuro
      const overlay = document.createElement('div');
      overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        animation: fadeIn 0.4s ease;
      `;
      
      // Crear contenedor del popup
      const popup = document.createElement('div');
      popup.style.cssText = `
        background: white;
        padding: 2.5rem;
        border-radius: 16px;
        text-align: center;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        animation: scaleUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      `;
      
      // Contenido del popup
      popup.innerHTML = `
        <div style="width: 80px; height: 80px; background: #EECF6D; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 1.5rem; box-shadow: 0 8px 20px rgba(238, 207, 109, 0.4);">
          <span style="font-size: 2.5rem; color: #45050C;">✓</span>
        </div>
        <h2 style="color: #45050C; margin-bottom: 1rem; font-size: 1.8rem;">¡Registro exitoso!</h2>
        <p style="color: #555; margin-bottom: 1.5rem; font-size: 1.1rem;">
          Tu cuenta ha sido creada correctamente.<br>
          <strong><?= htmlspecialchars($_SESSION['registered_email']) ?></strong>
        </p>
        <div style="background: #f8f9fa; padding: 1rem; border-radius: 10px; margin: 1.5rem 0;">
          <p style="color: #720E07; margin: 0; font-weight: 600;">
            Redirigiendo al login en <span id="countdown">3</span> segundos...
          </p>
        </div>
        <button onclick="window.location.href='login.php'" 
          style="background: #720E07; color: white; border: none; padding: 1rem 2rem; border-radius: 12px; font-weight: 700; font-size: 1.1rem; cursor: pointer; transition: all 0.3s; width: 100%;">
          Ir al Login ahora
        </button>
      `;
      
      // Añadir elementos al DOM
      overlay.appendChild(popup);
      document.body.appendChild(overlay);
      
      // Animaciones CSS
      const style = document.createElement('style');
      style.textContent = `
        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }
        @keyframes scaleUp {
          from { transform: scale(0.8); opacity: 0; }
          to { transform: scale(1); opacity: 1; }
        }
      `;
      document.head.appendChild(style);
      
      // Contador de redirección automática
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
      
      // Eliminar la variable de sesión
      <?php unset($_SESSION['registered_email']); ?>
    });
  </script>
  <?php endif; ?>
  <script src="assets/mobile-header.js?v=20260211-6"></script>
</body>
</html>

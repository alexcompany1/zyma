<?php
/**
 * login.php
 * Inicio de sesion.
 */

session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $workerCode = trim($_POST['workerCode'] ?? '');

    if (empty($email) || empty($password)) {
        $error = "El email y la contraseña son obligatorios.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, email, password_hash, worker_code FROM usuarios WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['worker_code'] = $user['worker_code'];
                
                if (!empty($workerCode)) {
                    if (!empty($user['worker_code']) && hash_equals($user['worker_code'], $workerCode)) {
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
                    header('Location: usuario.php');
                    exit;
                }
            } else {
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
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <header>
      <div class="header-content">
        <h1 class="logo">Zyma</h1>
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
</body>
</html>

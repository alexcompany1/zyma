<?php
/**
 * admin.php
 * Panel de administracion de usuarios (solo ADMIN).
 */

session_start();
require_once 'config.php';

// Permisos

// Validar acceso admin
if (!isset($_SESSION['user_id']) || ($_SESSION['worker_code'] ?? '') !== 'ADMIN') {
    die("<h2 class='page-error'>Acceso denegado. Solo administradores.</h2>");
}

$msg = ''; // Mensaje para la vista
'; // Mensaje para la vista
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {

    // Leer datos del formulario
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $worker_code = trim($_POST['worker_code'] ?? '');

    // Validacion basica
    if ($email === '' || $password === '') {
        $msg = "<div class='alert alert-error'>Email y contraseña obligatorios.</div>";
    } else {
        try {
            // Generar hash de password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Guardar usuario
            $stmt = $pdo->prepare("
                INSERT INTO usuarios (email, password_hash, worker_code)
                VALUES (:email, :password_hash, :worker_code)
            ");
            $stmt->execute([
                ':email' => $email,
                ':password_hash' => $password_hash,
                ':worker_code' => $worker_code !== '' ? $worker_code : null
            ]);

            $msg = "<div class='alert alert-success'>Usuario creado correctamente.</div>";

        } catch (Exception $e) {
            // Email duplicado u otro error
            if ($e->getCode() == 23000) {
                $msg = "<div class='alert alert-error'>El email ya existe.</div>";
            } else {
                $msg = "<div class='alert alert-error'>Error al crear usuario.</div>";
            }
        }
    }
}

// Eliminar usuario
if (isset($_GET['delete'])) {

    // Validar id recibido
    $id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);

    if (!$id) {
        $msg = "<div class='alert alert-error'>ID inválido.</div>";

    // Evitar borrar el propio usuario
    } elseif ($id === $_SESSION['user_id']) {
        $msg = "<div class='alert alert-error'>No puedes eliminar tu propio usuario.</div>";

    } else {
        try {
            // Eliminar si no es ADMIN
            $stmt = $pdo->prepare("
                DELETE FROM usuarios
                WHERE id = :id
                AND (worker_code IS NULL OR worker_code != 'ADMIN')
            ");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Redireccion tras borrar
                header('Location: admin.php?msg=eliminado');
                exit;
            }

            // Si no se elimino, mostrar motivo
            $check = $pdo->prepare("
                SELECT email, worker_code
                FROM usuarios
                WHERE id = :id
            ");
            $check->execute([':id' => $id]);
            $user = $check->fetch();

            if ($user) {
                $msg = "<div class='alert alert-error'>
                        No se puede eliminar el usuario " . htmlspecialchars($user['email']) . ".
                        </div>";
            } else {
                $msg = "<div class='alert alert-error'>Usuario no encontrado.</div>";
            }

        } catch (Exception $e) {
            $msg = "<div class='alert alert-error'>Error en la base de datos.</div>";
        }
    }
}

// Mensajes
if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado') {
    $msg = "<div class='alert alert-success'>Usuario eliminado correctamente.</div>";
}

// Listado de usuarios
$stmt = $pdo->query("
    SELECT id, email, worker_code
    FROM usuarios
    ORDER BY id
");
$usuarios = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="admin-header">
    <h1>Panel de Administración</h1>
    <a href="logout.php" class="admin-link">Cerrar sesión</a>
</header>

<div class="container">

    <!-- Mensajes -->
    <?= $msg ?>

    <!-- Crear usuario -->
    <div class="section-card">
        <h2>Añadir usuario</h2>

        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="text" name="worker_code" placeholder="Código / Rol (opcional)">
            <button type="submit" name="add">Crear usuario</button>
        </form>
    </div>

    <!-- Lista de usuarios -->
    <div class="section-card">
        <h2>Usuarios registrados (<?= count($usuarios) ?>)</h2>

        <table border="1" cellpadding="8" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Código / Rol</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['worker_code'] ?? '—') ?></td>
                        <td>
                            <?php if ($u['worker_code'] !== 'ADMIN'): ?>
                                <a href="?delete=<?= $u['id'] ?>"
                                   onclick="return confirm('¿Eliminar este usuario?')">
                                    Eliminar
                                </a>
                            <?php else: ?>
                                Administrador
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>



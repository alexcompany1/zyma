<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
?>
<?php
/**
 * admin.php
 * Panel de administracion de usuarios (solo ADMIN).
 */

session_start();
require_once 'config.php';

function hasTableColumn(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = :table_name
          AND COLUMN_NAME = :column_name
    ");
    $stmt->execute([
        ':table_name' => $table,
        ':column_name' => $column
    ]);

    return (int)$stmt->fetchColumn() > 0;
}

function roleFromWorkerCode(?string $workerCode): string
{
    if ($workerCode === 'ADMIN') {
        return 'admin';
    }
    if ($workerCode === null || $workerCode === '') {
        return 'cliente';
    }
    return 'trabajador';
}

function workerCodeFromRole(string $role, int $userId, ?string $customCode = null): ?string
{
    if ($role === 'admin') {
        return 'ADMIN';
    }
    if ($role === 'cliente') {
        return null;
    }
    if ($customCode !== null && trim($customCode) !== '') {
        return trim($customCode);
    }
    return 'TRAB' . str_pad((string)$userId, 3, '0', STR_PAD_LEFT);
}

function isValidWorkerCode(string $code): bool
{
    return (bool)preg_match('/^[A-Z0-9_-]{3,32}$/', $code);
}

function workerCodeExists(PDO $pdo, string $workerCode, ?int $excludeId = null): bool
{
    $sql = "SELECT COUNT(*) FROM usuarios WHERE worker_code = :worker_code";
    $params = [':worker_code' => $workerCode];
    if ($excludeId !== null) {
        $sql .= " AND id != :exclude_id";
        $params[':exclude_id'] = $excludeId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn() > 0;
}

function generateUniqueWorkerCode(PDO $pdo, int $userId): string
{
    $attempt = 0;
    do {
        $suffix = str_pad((string)($userId + $attempt), 3, '0', STR_PAD_LEFT);
        $code = 'TRAB' . $suffix;
        $attempt++;
    } while (workerCodeExists($pdo, $code) && $attempt < 10000);

    return $code;
}

function isSafeIdentifier(string $identifier): bool
{
    return (bool)preg_match('/^[A-Za-z0-9_]+$/', $identifier);
}

function deleteUserCascade(PDO $pdo, int $userId): bool
{
    $pdo->beginTransaction();

    try {
        // 1) Eliminar tablas que referencian pedidos, para luego poder borrar pedidos del usuario.
        $stmtRefsPedidos = $pdo->query("
            SELECT TABLE_NAME, COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
              AND REFERENCED_TABLE_NAME = 'pedidos'
              AND REFERENCED_COLUMN_NAME = 'id_pedido'
        ");
        $refsPedidos = $stmtRefsPedidos->fetchAll(PDO::FETCH_ASSOC);

        foreach ($refsPedidos as $ref) {
            $table = (string)$ref['TABLE_NAME'];
            $column = (string)$ref['COLUMN_NAME'];
            if (!isSafeIdentifier($table) || !isSafeIdentifier($column)) {
                continue;
            }
            $sql = "DELETE child FROM `$table` child INNER JOIN pedidos p ON child.`$column` = p.id_pedido WHERE p.id_usuario = :id_usuario";
            $stmtDeleteChild = $pdo->prepare($sql);
            $stmtDeleteChild->execute([':id_usuario' => $userId]);
        }

        // 2) Eliminar pedidos del usuario.
        $stmtDeletePedidos = $pdo->prepare("DELETE FROM pedidos WHERE id_usuario = :id_usuario");
        $stmtDeletePedidos->execute([':id_usuario' => $userId]);

        // 3) Eliminar tablas que referencian directamente usuarios (excepto pedidos, ya tratado).
        $stmtRefsUsuarios = $pdo->query("
            SELECT TABLE_NAME, COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
              AND REFERENCED_TABLE_NAME = 'usuarios'
              AND REFERENCED_COLUMN_NAME = 'id'
        ");
        $refsUsuarios = $stmtRefsUsuarios->fetchAll(PDO::FETCH_ASSOC);

        foreach ($refsUsuarios as $ref) {
            $table = (string)$ref['TABLE_NAME'];
            $column = (string)$ref['COLUMN_NAME'];
            if ($table === 'pedidos') {
                continue;
            }
            if (!isSafeIdentifier($table) || !isSafeIdentifier($column)) {
                continue;
            }
            $sql = "DELETE FROM `$table` WHERE `$column` = :id_usuario";
            $stmtDeleteDirect = $pdo->prepare($sql);
            $stmtDeleteDirect->execute([':id_usuario' => $userId]);
        }

        // 4) Eliminar usuario.
        $stmtDeleteUser = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmtDeleteUser->execute([':id' => $userId]);

        if ($stmtDeleteUser->rowCount() !== 1) {
            $pdo->rollBack();
            return false;
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

if (!isset($_SESSION['user_id']) || ($_SESSION['worker_code'] ?? '') !== 'ADMIN') {
    die("<h2 class='page-error'>Acceso denegado. Solo administradores.</h2>");
}

$supportsBloqueado = hasTableColumn($pdo, 'usuarios', 'bloqueado');
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'cliente';
    $workerCodeInput = trim($_POST['worker_code'] ?? '');

    if ($nombre === '' || $email === '' || $password === '') {
        $msg = "<div class='alert alert-error'>Nombre, email y contrasena obligatorios.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "<div class='alert alert-error'>Formato de email invalido.</div>";
    } elseif (strlen($password) < 6) {
        $msg = "<div class='alert alert-error'>La contrasena debe tener al menos 6 caracteres.</div>";
    } elseif (!in_array($role, ['cliente', 'trabajador', 'admin'], true)) {
        $msg = "<div class='alert alert-error'>Rol invalido.</div>";
    } elseif ($workerCodeInput !== '' && !isValidWorkerCode($workerCodeInput)) {
        $msg = "<div class='alert alert-error'>Codigo trabajador invalido. Usa 3-32 caracteres A-Z, 0-9, _ o -.</div>";
    } elseif ($workerCodeInput !== '' && workerCodeExists($pdo, $workerCodeInput)) {
        $msg = "<div class='alert alert-error'>Ese codigo de trabajador ya esta en uso.</div>";
    } else {
        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $workerCode = null;
            if ($role === 'admin') {
                $workerCode = 'ADMIN';
            }
            if ($role === 'trabajador' && $workerCodeInput !== '') {
                $workerCode = $workerCodeInput;
            }

            $sqlInsert = "INSERT INTO usuarios (nombre, email, password_hash, worker_code" . ($supportsBloqueado ? ", bloqueado" : "") . ") VALUES (:nombre, :email, :password_hash, :worker_code" . ($supportsBloqueado ? ", 0" : "") . ")";
            $stmt = $pdo->prepare($sqlInsert);
            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':password_hash' => $passwordHash,
                ':worker_code' => $workerCode
            ]);

            $newUserId = (int)$pdo->lastInsertId();
            if ($role === 'trabajador' && $workerCode === null) {
                $generatedCode = generateUniqueWorkerCode($pdo, $newUserId);
                $stmt = $pdo->prepare("UPDATE usuarios SET worker_code = :worker_code WHERE id = :id");
                $stmt->execute([
                    ':worker_code' => $generatedCode,
                    ':id' => $newUserId
                ]);
                $msg = "<div class='alert alert-success'>Usuario creado correctamente. Codigo trabajador: " . htmlspecialchars($generatedCode) . ".</div>";
            } else {
                $msg = "<div class='alert alert-success'>Usuario creado correctamente.</div>";
            }
        } catch (Exception $e) {
            if ((string)$e->getCode() === '23000') {
                $msg = "<div class='alert alert-error'>El email ya existe.</div>";
            } else {
                $msg = "<div class='alert alert-error'>Error al crear usuario.</div>";
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = (string)($_POST['action'] ?? '');
    $targetId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    if (!in_array($action, ['delete', 'role', 'toggle_block'], true)) {
        $msg = "<div class='alert alert-error'>Accion invalida.</div>";
    } elseif (!$targetId) {
        $msg = "<div class='alert alert-error'>ID invalido.</div>";
    } elseif ($targetId === (int)$_SESSION['user_id']) {
        $msg = "<div class='alert alert-error'>No puedes aplicar esta accion sobre tu propio usuario.</div>";
    } else {
        try {
            $stmtUser = $pdo->prepare("SELECT id FROM usuarios WHERE id = :id");
            $stmtUser->execute([':id' => $targetId]);
            $targetExists = (bool)$stmtUser->fetchColumn();
            if (!$targetExists) {
                $msg = "<div class='alert alert-error'>Usuario no encontrado.</div>";
            } elseif ($action === 'delete') {
                try {
                    if (deleteUserCascade($pdo, (int)$targetId)) {
                        $msg = "<div class='alert alert-success'>Usuario eliminado correctamente.</div>";
                    } else {
                        $msg = "<div class='alert alert-error'>No se pudo eliminar el usuario.</div>";
                    }
                } catch (Exception $e) {
                    $msg = "<div class='alert alert-error'>No se pudo eliminar la cuenta. Detalle: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
            } elseif ($action === 'role') {
                $newRole = $_POST['new_role'] ?? '';
                if (!in_array($newRole, ['cliente', 'trabajador', 'admin'], true)) {
                    $msg = "<div class='alert alert-error'>Rol invalido.</div>";
                } else {
                    $customCode = trim($_POST['new_worker_code'] ?? '');
                    if ($newRole === 'trabajador' && $customCode !== '' && !isValidWorkerCode($customCode)) {
                        $msg = "<div class='alert alert-error'>Codigo trabajador invalido. Usa 3-32 caracteres A-Z, 0-9, _ o -.</div>";
                    } elseif ($customCode !== '' && workerCodeExists($pdo, $customCode, (int)$targetId)) {
                        $msg = "<div class='alert alert-error'>Ese codigo de trabajador ya esta en uso.</div>";
                    } else {
                        $newWorkerCode = workerCodeFromRole($newRole, (int)$targetId, $customCode !== '' ? $customCode : null);
                        if ($newRole === 'trabajador' && $customCode === '') {
                            $newWorkerCode = generateUniqueWorkerCode($pdo, (int)$targetId);
                        }

                        $stmt = $pdo->prepare("UPDATE usuarios SET worker_code = :worker_code WHERE id = :id");
                        $stmt->execute([
                            ':worker_code' => $newWorkerCode,
                            ':id' => $targetId
                        ]);
                        $msg = "<div class='alert alert-success'>Rol actualizado correctamente.</div>";
                    }
                }
            } elseif ($action === 'toggle_block') {
                if (!$supportsBloqueado) {
                    $msg = "<div class='alert alert-error'>Falta columna bloqueado en tabla usuarios.</div>";
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET bloqueado = CASE WHEN bloqueado = 1 THEN 0 ELSE 1 END WHERE id = :id");
                    $stmt->execute([':id' => $targetId]);
                    $msg = $stmt->rowCount() > 0
                        ? "<div class='alert alert-success'>Estado de bloqueo actualizado.</div>"
                        : "<div class='alert alert-error'>No se puede bloquear/desbloquear ese usuario.</div>";
                }
            }
        } catch (Exception $e) {
            $msg = "<div class='alert alert-error'>Error en la base de datos.</div>";
        }
    }
}

$sqlUsuarios = "SELECT id, nombre, email, worker_code" . ($supportsBloqueado ? ", bloqueado" : ", 0 AS bloqueado") . " FROM usuarios ORDER BY id";
$stmt = $pdo->query($sqlUsuarios);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administracion</title>
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
    <?= $msg ?>

    <?php if (!$supportsBloqueado): ?>
      <div class="alert alert-error">Bloqueo no disponible. Ejecuta: ALTER TABLE usuarios ADD COLUMN bloqueado TINYINT(1) NOT NULL DEFAULT 0;</div>
    <?php endif; ?>

    <div class="section-card">
        <h2>Anadir usuario</h2>

        <form method="POST">
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Contrasena" required>
            <select name="role" required>
                <option value="cliente">Cliente</option>
                <option value="trabajador">Trabajador</option>
                <option value="admin">Admin</option>
            </select>
            <input type="text" name="worker_code" placeholder="Codigo trabajador (opcional)">
            <button type="submit" name="add">Crear usuario</button>
        </form>
    </div>

    <div class="section-card">
        <h2>Usuarios registrados (<?= count($usuarios) ?>)</h2>
        <div class="admin-table-wrap">
            <table class="admin-users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Codigo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <?php
                        $rol = roleFromWorkerCode($u['worker_code'] ?? null);
                        $isAdmin = ($u['worker_code'] ?? '') === 'ADMIN';
                        $isSelf = ((int)$u['id'] === (int)$_SESSION['user_id']);
                        $isBlocked = (int)($u['bloqueado'] ?? 0) === 1;
                        ?>
                        <tr>
                            <td><?= (int)$u['id'] ?></td>
                            <td><?= htmlspecialchars($u['nombre'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($rol)) ?></td>
                            <td><?= htmlspecialchars(($u['worker_code'] ?? '') !== '' ? $u['worker_code'] : '-') ?></td>
                            <td>
                                <span class="badge-status <?= $isBlocked ? 'badge-estado-cancelado' : 'badge-estado-listo' ?>">
                                    <?= $isBlocked ? 'Bloqueado' : 'Activo' ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!$isSelf): ?>
                                    <div class="admin-action-row">
                                        <form method="POST" class="admin-action-form admin-role-form">
                                            <input type="hidden" name="action" value="role">
                                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                            <select name="new_role">
                                                <option value="cliente" <?= $rol === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                                                <option value="trabajador" <?= $rol === 'trabajador' ? 'selected' : '' ?>>Trabajador</option>
                                                <option value="admin" <?= $rol === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                            <input type="text" name="new_worker_code" placeholder="Codigo opcional">
                                            <button type="submit">Guardar rol</button>
                                        </form>

                                        <form method="POST" class="admin-action-form">
                                            <input type="hidden" name="action" value="toggle_block">
                                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                            <button type="submit" <?= !$supportsBloqueado ? 'disabled' : '' ?>>
                                                <?= $isBlocked ? 'Desbloquear' : 'Bloquear' ?>
                                            </button>
                                        </form>

                                        <form method="POST" class="admin-action-form" onsubmit="return confirm('Eliminar usuario?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                            <button type="submit">Eliminar</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="admin-self-label">Tu usuario</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const profileBtn = document.getElementById('profileBtn');
const dropdownMenu = document.getElementById('dropdownMenu');
const quickDropdown = document.getElementById('quickDropdown');
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
</body>
</html>

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
    } elseif (!in_array($role, ['cliente', 'trabajador', 'admin'], true)) {
        $msg = "<div class='alert alert-error'>Rol invalido.</div>";
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
                $generatedCode = workerCodeFromRole('trabajador', $newUserId);
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
    $action = $_POST['action'];
    $targetId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    if (!$targetId) {
        $msg = "<div class='alert alert-error'>ID invalido.</div>";
    } elseif ($targetId === (int)$_SESSION['user_id']) {
        $msg = "<div class='alert alert-error'>No puedes aplicar esta accion sobre tu propio usuario.</div>";
    } else {
        try {
            if ($action === 'delete') {
                $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id AND (worker_code IS NULL OR worker_code != 'ADMIN')");
                $stmt->execute([':id' => $targetId]);
                $msg = $stmt->rowCount() > 0
                    ? "<div class='alert alert-success'>Usuario eliminado correctamente.</div>"
                    : "<div class='alert alert-error'>No se puede eliminar ese usuario.</div>";
            }

            if ($action === 'role') {
                $newRole = $_POST['new_role'] ?? '';
                if (!in_array($newRole, ['cliente', 'trabajador', 'admin'], true)) {
                    $msg = "<div class='alert alert-error'>Rol invalido.</div>";
                } else {
                    $customCode = trim($_POST['new_worker_code'] ?? '');
                    $newWorkerCode = workerCodeFromRole($newRole, (int)$targetId, $customCode !== '' ? $customCode : null);

                    $stmt = $pdo->prepare("UPDATE usuarios SET worker_code = :worker_code WHERE id = :id");
                    $stmt->execute([
                        ':worker_code' => $newWorkerCode,
                        ':id' => $targetId
                    ]);
                    $msg = "<div class='alert alert-success'>Rol actualizado correctamente.</div>";
                }
            }

            if ($action === 'toggle_block') {
                if (!$supportsBloqueado) {
                    $msg = "<div class='alert alert-error'>Falta columna bloqueado en tabla usuarios.</div>";
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET bloqueado = CASE WHEN bloqueado = 1 THEN 0 ELSE 1 END WHERE id = :id AND (worker_code IS NULL OR worker_code != 'ADMIN')");
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
          <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4 1.79 4 4 4zm0 2c1.52 0 5.1 1.34 5.1 5v1H6.9v-1c0-3.66 3.58-5 5.1-5z"/>
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

        <table width="100%">
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
                        <td><?= $isBlocked ? 'Bloqueado' : 'Activo' ?></td>
                        <td>
                            <?php if (!$isSelf): ?>
                                <form method="POST" style="display:inline-flex; gap:8px; align-items:center; margin: 4px 0;">
                                    <input type="hidden" name="action" value="role">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                    <select name="new_role">
                                        <option value="cliente" <?= $rol === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                                        <option value="trabajador" <?= $rol === 'trabajador' ? 'selected' : '' ?>>Trabajador</option>
                                        <option value="admin" <?= $rol === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                    <input type="text" name="new_worker_code" placeholder="Codigo (si trabajador)">
                                    <button type="submit">Guardar rol</button>
                                </form>

                                <form method="POST" style="display:inline-flex; gap:8px; align-items:center; margin: 4px 0;">
                                    <input type="hidden" name="action" value="toggle_block">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                    <button type="submit" <?= (!$supportsBloqueado || $isAdmin) ? 'disabled' : '' ?>>
                                        <?= $isBlocked ? 'Desbloquear' : 'Bloquear' ?>
                                    </button>
                                </form>

                                <form method="POST" style="display:inline-flex; margin: 4px 0;" onsubmit="return confirm('Eliminar usuario?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                    <button type="submit" <?= $isAdmin ? 'disabled' : '' ?>>Eliminar</button>
                                </form>
                            <?php else: ?>
                                Tu usuario
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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

const AUTO_REFRESH_MS = 1200;
let hasPendingInput = false;

document.querySelectorAll('form input, form textarea, form select').forEach((el) => {
  el.addEventListener('input', () => { hasPendingInput = true; });
  el.addEventListener('change', () => { hasPendingInput = true; });
  el.addEventListener('blur', () => { hasPendingInput = false; });
});

document.querySelectorAll('form').forEach((form) => {
  form.addEventListener('submit', () => { hasPendingInput = false; });
});

setInterval(() => {
  if (document.hidden) return;
  if (hasPendingInput) return;
  if (dropdownMenu && dropdownMenu.classList.contains('show')) return;
    if (quickDropdown && quickDropdown.classList.contains('show')) return;
  const active = document.activeElement;
  if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT')) return;
  window.location.reload();
}, AUTO_REFRESH_MS);
</script>
<script src="assets/mobile-header.js?v=20260211-6"></script>
</body>
</html>

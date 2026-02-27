<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
?>
<?php
/**
 * notificaciones.php
 * Listado de notificaciones del usuario.
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config.php';

$alert = '';
$user_id = (int)$_SESSION['user_id'];
$notifIdCol = 'id';

try {
    $stmtCol = $pdo->query("
        SELECT COLUMN_NAME
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'notificaciones'
          AND COLUMN_NAME IN ('id', 'id_notificacion')
    ");
    $cols = $stmtCol->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('id', $cols, true)) {
        $notifIdCol = 'id';
    } elseif (in_array('id_notificacion', $cols, true)) {
        $notifIdCol = 'id_notificacion';
    }
} catch (Exception $e) {
    $notifIdCol = 'id';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_as_read_respuesta'])) {
        $notif_id = (int)$_POST['mark_as_read_respuesta'];
        
        try {
            $stmt = $pdo->prepare("UPDATE notificaciones_respuestas SET leida = 1 WHERE id = :notif_id AND id_usuario = :id_usuario");
            $stmt->execute([':notif_id' => $notif_id, ':id_usuario' => $user_id]);
            
            header('Location: notificaciones.php?msg=leida');
            exit;
        } catch (Exception $e) {
            error_log("Error marcando respuesta como leída: " . $e->getMessage());
        }
    }
}

if (isset($_GET['read'])) {
    $id = (int)($_GET['read'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("
            UPDATE notificaciones
            SET leida = 1
            WHERE {$notifIdCol} = :id AND id_usuario = :id_usuario
        ");
        $stmt->execute([':id' => $id, ':id_usuario' => $user_id]);

        header('Location: notificaciones.php?msg=leida');
        exit;
    }
}

if (isset($_GET['read_all'])) {
    $stmt = $pdo->prepare("
        UPDATE notificaciones
        SET leida = 1
        WHERE id_usuario = :id_usuario AND leida = 0
    ");
    $stmt->execute([':id_usuario' => $user_id]);
    
    // También marcar respuestas como leídas
    try {
        $stmtResp = $pdo->prepare("
            UPDATE notificaciones_respuestas
            SET leida = 1
            WHERE id_usuario = :id_usuario AND leida = 0
        ");
        $stmtResp->execute([':id_usuario' => $user_id]);
    } catch (Exception $e) {
        error_log("Error marcando respuestas como leídas: " . $e->getMessage());
    }
    
    header('Location: notificaciones.php?msg=leidas');
    exit;
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'leida') {
        $alert = "<div class='alert alert-success'>Notificación marcada como leída.</div>";
    }
    if ($_GET['msg'] === 'leidas') {
        $alert = "<div class='alert alert-success'>Todas las notificaciones fueron marcadas como leídas.</div>";
    }
}

$stmt = $pdo->prepare("
    SELECT {$notifIdCol} AS notif_id, mensaje, leida, fecha
    FROM notificaciones
    WHERE id_usuario = :id_usuario
    ORDER BY fecha DESC, {$notifIdCol} DESC
");
$stmt->execute([':id_usuario' => $user_id]);
$notificaciones = $stmt->fetchAll();

// Obtener notificaciones de respuestas a valoraciones
$notificaciones_respuestas = [];
try {
    $stmt = $pdo->prepare("
        SELECT nr.id, p.nombre as producto, v.puntuacion, rv.respuesta, nr.leida, nr.fecha_creacion
        FROM notificaciones_respuestas nr
        JOIN valoraciones v ON nr.id_valoracion = v.id
        JOIN productos p ON v.id_producto = p.id
        LEFT JOIN respuestas_valoraciones rv ON v.id = rv.id_valoracion
        WHERE nr.id_usuario = :id_usuario
        ORDER BY nr.fecha_creacion DESC
    ");
    $stmt->execute([':id_usuario' => $user_id]);
    $notificaciones_respuestas = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error obteniendo notificaciones de respuestas: " . $e->getMessage());
}

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM notificaciones
    WHERE id_usuario = :id_usuario AND leida = 0
");
$stmt->execute([':id_usuario' => $user_id]);
$unread_count = (int)$stmt->fetchColumn();

// Contar notificaciones de respuestas no leídas
try {
    $stmtResp = $pdo->prepare("
        SELECT COUNT(*)
        FROM notificaciones_respuestas
        WHERE id_usuario = :id_usuario AND leida = 0
    ");
    $stmtResp->execute([':id_usuario' => $user_id]);
    $unread_respuestas = (int)$stmtResp->fetchColumn();
    $unread_count += $unread_respuestas;
} catch (Exception $e) {
    error_log("Error contando respuestas no leídas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zyma - Notificaciones</title>
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

<main class="main-content">
  <h1 class="welcome">Notificaciones</h1>
  <p class="muted">Aquí verás los avisos sobre tus pedidos y actualizaciones.</p>

  <?= $alert ?>

  <div class="row-between mt-2">
    <span class="muted">No leídas: <strong><?= $unread_count ?></strong></span>
    <?php if ($unread_count > 0): ?>
      <a href="?read_all=1" class="btn-seguir-comprando">Marcar todas como leídas</a>
    <?php endif; ?>
  </div>

  <div class="section mt-3">
    <?php
    $todas_notificaciones = $notificaciones;
    
    // Agregar notificaciones de respuestas en el mismo array para ordenarlas por fecha
    foreach ($notificaciones_respuestas as $resp) {
        $todas_notificaciones[] = [
            'notif_id' => $resp['id'],
            'mensaje' => '👤 El restaurante Zyma respondió a tu reseña de ' . htmlspecialchars($resp['producto']),
            'fecha' => $resp['fecha_creacion'],
            'leida' => $resp['leida'],
            'tipo' => 'respuesta',
            'respuesta_texto' => $resp['respuesta']
        ];
    }
    
    // Ordenar por fecha descendente
    usort($todas_notificaciones, function($a, $b) {
        return strtotime($b['fecha']) - strtotime($a['fecha']);
    });
    ?>
    
    <?php if (count($todas_notificaciones) === 0): ?>
      <div class="empty-state">No tienes notificaciones todavía.</div>
    <?php else: ?>
      <?php foreach ($todas_notificaciones as $n): ?>
        <div class="notif-item <?= $n['leida'] ? '' : 'notif-unread' ?>">
          <div>
            <p class="notif-message"><?= htmlspecialchars($n['mensaje']) ?></p>
            <?php if (isset($n['respuesta_texto']) && $n['respuesta_texto']): ?>
              <div style="background-color: #f5f5f5; padding: 12px; margin-top: 10px; border-radius: 6px; border-left: 3px solid #d4af37;">
                <p style="margin: 0; font-size: 0.9em; color: #555;"><strong>Respuesta:</strong></p>
                <p style="margin: 8px 0 0 0; font-size: 0.9em; color: #333;"><?= htmlspecialchars($n['respuesta_texto']) ?></p>
              </div>
            <?php endif; ?>
            <p class="muted notif-date"><?= htmlspecialchars($n['fecha']) ?></p>
          </div>
          <?php if ((int)$n['leida'] === 0): ?>
            <?php if (isset($n['tipo']) && $n['tipo'] === 'respuesta'): ?>
              <form method="POST" style="display: inline;">
                <input type="hidden" name="mark_as_read_respuesta" value="<?= (int)$n['notif_id'] ?>">
                <button type="submit" class="btn-seguir-comprando" style="border: none; cursor: pointer; padding: 8px 16px; background-color: #d4af37; color: #333; border-radius: 4px; font-weight: 500;">Marcar como leída</button>
              </form>
            <?php else: ?>
              <a class="btn-seguir-comprando" href="?read=<?= (int)$n['notif_id'] ?>">Marcar como leída</a>
            <?php endif; ?>
          <?php else: ?>
            <span class="badge-status badge-estado-listo">Leída</span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

<footer>
  <p>© 2025 Zyma. Todos los derechos reservados.</p>
</footer>

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

const AUTO_REFRESH_MS = 800;
setInterval(() => {
  if (document.hidden) return;
  if (dropdownMenu && dropdownMenu.classList.contains('show')) return;
    if (quickDropdown && quickDropdown.classList.contains('show')) return;
  window.location.reload();
}, AUTO_REFRESH_MS);
</script>
<script src="assets/mobile-header.js?v=20260211-6"></script>
</body>
</html>

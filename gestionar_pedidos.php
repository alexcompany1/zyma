<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
?>
<?php
/**
 * gestionar_pedidos.php
 * Gestiona pedidos y estados.
 */

session_start();
require_once 'config.php';

function crear_notificacion(PDO $pdo, int $pedidoId, string $mensaje): void
{
    $stmt = $pdo->prepare("SELECT id_usuario FROM pedidos WHERE id_pedido = ?");
    $stmt->execute([$pedidoId]);
    $id_usuario = $stmt->fetchColumn();

    if ($id_usuario) {
        $stmt = $pdo->prepare("
            INSERT INTO notificaciones (id_usuario, mensaje, leida, fecha)
            VALUES (?, ?, 0, NOW())
        ");
        $stmt->execute([$id_usuario, $mensaje]);
    }
}

// Validar acceso trabajador
if (!isset($_SESSION['user_id']) || !$_SESSION['worker_code']) {
    die("<h2 class='page-error'>Acceso denegado. Solo trabajadores.</h2>");
}

// Cancelar pedido 
if (isset($_GET['accion']) && $_GET['accion'] === 'cancelar' && isset($_GET['id'])) {
    $pedidoId = intval($_GET['id']);
    
    try {
        // Marcar como cancelado 
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = 'cancelado' WHERE id_pedido = ?");
        $stmt->execute([$pedidoId]);

        crear_notificacion($pdo, $pedidoId, "Tu pedido #{$pedidoId} ha sido cancelado.");
        
        header('Location: gestionar_pedidos.php');
        exit;
        
    } catch (Exception $e) {
        die("<h2 class='page-error'>Error al cancelar: " . htmlspecialchars($e->getMessage()) . "</h2>");
    }
}

// Marcar como preparando y actualizar stock
if (isset($_GET['accion']) && $_GET['accion'] === 'preparando' && isset($_GET['id'])) {
    $pedidoId = intval($_GET['id']);
    
    try {
        $pdo->beginTransaction();

        // Evita doble descuento: solo procesa si el pedido sigue en pendiente.
        $stmtEstado = $pdo->prepare("
            SELECT estado
            FROM pedidos
            WHERE id_pedido = ?
            FOR UPDATE
        ");
        $stmtEstado->execute([$pedidoId]);
        $estadoActual = $stmtEstado->fetchColumn();

        if ($estadoActual !== 'pendiente') {
            $pdo->rollBack();
            header('Location: gestionar_pedidos.php');
            exit;
        }

        // Leer items
        $stmt = $pdo->prepare("
            SELECT id_producto, cantidad 
            FROM pedido_items 
            WHERE id_pedido = ?
        ");
        $stmt->execute([$pedidoId]);
        $items_pedido = $stmt->fetchAll();
        
        // Actualizar stock
        foreach ($items_pedido as $item) {
            $producto_id = $item['id_producto'];
            $cantidad_pedido = $item['cantidad'];
            
            // Ingredientes del producto
            $stmt_ing = $pdo->prepare("
                SELECT ingrediente_id, cantidad_necesaria 
                FROM producto_ingredientes 
                WHERE producto_id = ?
            ");
            $stmt_ing->execute([$producto_id]);
            $ingredientes_producto = $stmt_ing->fetchAll();
            
            // Descontar ingrediente
            foreach ($ingredientes_producto as $ing) {
                $cantidad_necesaria = $ing['cantidad_necesaria'] * $cantidad_pedido;
                
                $stmt_update = $pdo->prepare("
                    UPDATE ingredientes 
                    SET cantidad = cantidad - ? 
                    WHERE id = ?
                ");
                $stmt_update->execute([$cantidad_necesaria, $ing['ingrediente_id']]);
            }
        }
        
        // Actualizar estado de forma segura para no repetir transición.
        $stmt = $pdo->prepare("
            UPDATE pedidos
            SET estado = 'preparando'
            WHERE id_pedido = ? AND estado = 'pendiente'
        ");
        $stmt->execute([$pedidoId]);
        if ($stmt->rowCount() !== 1) {
            $pdo->rollBack();
            header('Location: gestionar_pedidos.php');
            exit;
        }

        crear_notificacion($pdo, $pedidoId, "Tu pedido #{$pedidoId} está en preparación.");
        $pdo->commit();
        
        header('Location: gestionar_pedidos.php');
        exit;
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("<h2 class='page-error'>Error al preparar: " . htmlspecialchars($e->getMessage()) . "</h2>");
    }
}

// Marcar como listo
if (isset($_GET['accion']) && $_GET['accion'] === 'listo' && isset($_GET['id'])) {
    $pedidoId = intval($_GET['id']);

    try {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = 'listo' WHERE id_pedido = ?");
        $stmt->execute([$pedidoId]);

        crear_notificacion($pdo, $pedidoId, "Tu pedido #{$pedidoId} está listo para recoger.");

        header('Location: gestionar_pedidos.php');
        exit;
    } catch (Exception $e) {
        die("<h2 class='page-error'>Error al marcar como listo: " . htmlspecialchars($e->getMessage()) . "</h2>");
    }
}

// Marcar como entregando
if (isset($_GET['accion']) && $_GET['accion'] === 'entregando' && isset($_GET['id'])) {
    $pedidoId = intval($_GET['id']);

    try {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = 'entregando' WHERE id_pedido = ? AND estado = 'preparando'");
        $stmt->execute([$pedidoId]);

        if ($stmt->rowCount() === 1) {
            crear_notificacion($pdo, $pedidoId, "Tu pedido #{$pedidoId} está en camino de entrega.");
        }

        header('Location: gestionar_pedidos.php');
        exit;
    } catch (Exception $e) {
        die("<h2 class='page-error'>Error al marcar como entregando: " . htmlspecialchars($e->getMessage()) . "</h2>");
    }
}

// Marcar como entregado
if (isset($_GET['accion']) && $_GET['accion'] === 'entregado' && isset($_GET['id'])) {
    $pedidoId = intval($_GET['id']);

    try {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = 'entregado' WHERE id_pedido = ?");
        $stmt->execute([$pedidoId]);

        crear_notificacion($pdo, $pedidoId, "Tu pedido #{$pedidoId} ha sido entregado. ?Buen provecho!");

        header('Location: gestionar_pedidos.php');
        exit;
    } catch (Exception $e) {
        die("<h2 class='page-error'>Error al marcar como entregado: " . htmlspecialchars($e->getMessage()) . "</h2>");
    }
}

// Cargar pedidos
try {
    $stmt = $pdo->query("
        SELECT 
            id_pedido,
            total,
            estado,
            id_usuario,
            fecha_hora
        FROM pedidos 
        ORDER BY fecha_hora DESC, id_pedido DESC
    ");
    $pedidos = $stmt->fetchAll();
    
    // Cargar emails
    $emails = [];
    if (!empty($pedidos)) {
        $ids = array_column($pedidos, 'id_usuario');
        $ids_str = implode(',', $ids);
        $stmt_emails = $pdo->query("SELECT id, email FROM usuarios WHERE id IN ($ids_str)");
        $emails_data = $stmt_emails->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($emails_data as $email_row) {
            $emails[$email_row['id']] = $email_row['email'];
        }
    }
} catch (Exception $e) {
    $pedidos = [];
    $emails = [];
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zyma - Gestionar Pedidos</title>
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
    <div class="main-content">
        <h2 class="welcome">Gestionar Pedidos</h2>
        
        <!-- Boton volver -->
        <a href="trabajador.php" class="btn-volver-panel">Volver al Panel de Control</a>
        
        <?php if (empty($pedidos)): ?>
            <p class="empty-state">No hay pedidos.</p>
        <?php else: ?>
            <?php foreach ($pedidos as $pedido): ?>
            <div class="pedido-card estado-<?= $pedido['estado'] ?>">
                <div class="row-between mb-2">
                    <div>
                        <h3>Pedido #<?= $pedido['id_pedido'] ?></h3>
                        <p><strong>Cliente:</strong> <?= htmlspecialchars($emails[$pedido['id_usuario']] ?? 'Usuario desconocido') ?></p>
                        <p><strong>Total:</strong> ?<?= number_format($pedido['total'], 2, ',', '.') ?></p>
                        <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($pedido['fecha_hora'])) ?></p>
                    </div>
                    <div class="text-right">
                        <span class="badge-status badge-estado-<?= $pedido['estado'] ?>">
                            <?= ucfirst($pedido['estado']) ?>
                        </span>
                    </div>
                </div>
                
                <?php if ($pedido['estado'] === 'pendiente'): ?>
                    <div class="btn-row">
                        <!-- Acciones por estado -->
                        <a href="?accion=preparando&id=<?= $pedido['id_pedido'] ?>" class="btn-estado btn-preparando">Preparando</a>
                        <a href="?accion=cancelar&id=<?= $pedido['id_pedido'] ?>" class="btn-estado btn-cancelar">Cancelar</a>
                    </div>
                <?php elseif ($pedido['estado'] === 'preparando'): ?>
                    <div class="btn-row">
                        <a href="?accion=entregando&id=<?= $pedido['id_pedido'] ?>" class="btn-estado btn-entregando">Entregando</a>
                    </div>
                <?php elseif ($pedido['estado'] === 'entregando'): ?>
                    <div class="btn-row">
                        <a href="?accion=listo&id=<?= $pedido['id_pedido'] ?>" class="btn-estado btn-listo">Listo</a>
                    </div>
                <?php elseif ($pedido['estado'] === 'listo'): ?>
                    <div class="btn-row">
                        <a href="?accion=entregado&id=<?= $pedido['id_pedido'] ?>" class="btn-estado btn-entregado">Entregado</a>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
const profileBtn = document.getElementById('profileBtn');
const dropdownMenu = document.getElementById('dropdownMenu');
const quickDropdown = document.getElementById('quickDropdown');
profileBtn.addEventListener('click', () => dropdownMenu.classList.toggle('show'));
window.addEventListener('click', e => {
    if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove('show');
    }
});

// Auto-refresh para mantener datos al dia sin interrumpir interacciones.
const AUTO_REFRESH_MS = 800;
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

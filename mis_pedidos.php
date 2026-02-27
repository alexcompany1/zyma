<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
?>
<?php
/**
 * mis_pedidos.php
 * Lista pedidos del usuario.
 */

session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Cancelar pedido
if (isset($_GET['cancelar']) && isset($_GET['id'])) {
    $pedidoId = intval($_GET['id']);
    
    // Validar pedido del usuario
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id_pedido = ? AND id_usuario = ?");
    $stmt->execute([$pedidoId, $_SESSION['user_id']]);
    $pedido = $stmt->fetch();
    
    if ($pedido && in_array($pedido['estado'], ['pendiente', 'preparando'])) {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = 'cancelado' WHERE id_pedido = ?");
        $stmt->execute([$pedidoId]);
        
        $_SESSION['mensaje'] = "Pedido cancelado correctamente.";
    }
    
    header('Location: mis_pedidos.php');
    exit;
}

// Cargar pedidos
$stmt = $pdo->prepare("
    SELECT p.*, GROUP_CONCAT(pi.cantidad, 'x ', pr.nombre SEPARATOR ', ') as items
    FROM pedidos p 
    LEFT JOIN pedido_items pi ON p.id_pedido = pi.id_pedido
    LEFT JOIN productos pr ON pi.id_producto = pr.id
    WHERE p.id_usuario = ?
    GROUP BY p.id_pedido
    ORDER BY p.fecha_hora DESC, p.id_pedido DESC
");
$stmt->execute([$_SESSION['user_id']]);
$pedidos = $stmt->fetchAll();

$mensaje = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zyma - Mis Pedidos</title>
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
    <div class="main-content">
        <h2 class="welcome">Mis Pedidos</h2>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        
        <?php if (empty($pedidos)): ?>
            <p class="empty-state">No tienes pedidos realizados.</p>
            <div class="btn-row center">
                <a href="carta.php" class="btn-cart">Ver Carta</a>
            </div>
        <?php else: ?>
            <?php foreach ($pedidos as $pedido): ?>
            <div class="pedido-card estado-<?= $pedido['estado'] ?>">
                <div class="row-between mb-2">
                    <div>
                        <h3>Pedido #<?= htmlspecialchars($pedido['id_pedido']) ?></h3>
                        <p><strong>Estado:</strong> <?= ucfirst($pedido['estado']) ?></p>
                        <p><strong>Total:</strong> ?<?= number_format($pedido['total'], 2, ',', '.') ?></p>
                        <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($pedido['fecha_hora'])) ?></p>
                    </div>
                    <div class="text-right">
                        <span class="badge-status badge-estado-<?= $pedido['estado'] ?>">
                            <?= ucfirst($pedido['estado']) ?>
                        </span>
                    </div>
                </div>
                <p><strong>Productos:</strong> <?= htmlspecialchars($pedido['items']) ?></p>
                
                <?php if (in_array($pedido['estado'], ['pendiente', 'preparando'])): ?>
                    <button type="button" class="btn-cancelar" onclick="confirmarCancelacion(<?= htmlspecialchars($pedido['id_pedido']) ?>)">
                        Cancelar Pedido
                    </button>
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

function confirmarCancelacion(pedidoId) {
    if (confirm('Estas seguro de que quieres cancelar este pedido?')) {
        window.location.href = 'mis_pedidos.php?cancelar=1&id=' + pedidoId;
    }
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

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
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$pedidoId, $_SESSION['user_id']]);
    $pedido = $stmt->fetch();
    
    if ($pedido && in_array($pedido['estado'], ['pendiente', 'preparando'])) {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = 'cancelado' WHERE id = ?");
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
    LEFT JOIN pedido_items pi ON p.id = pi.pedido_id
    LEFT JOIN productos pr ON pi.producto_id = pr.id
    WHERE p.usuario_id = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
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
<link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <div class="profile-section">
        <button class="profile-btn" id="profileBtn">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="white">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c1.52 0 5.1 1.34 5.1 5v1H6.9v-1c0-3.66 3.58-5 5.1-5z"/>
            </svg>
        </button>
        <div class="dropdown" id="dropdownMenu">
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </div>
    <div class="header-content"><a href="usuario.php" class="logo">Zyma</a></div>
    <div class="cart-section">
        <a href="carrito.php" class="cart-btn">
            <img src="assets/cart-icon.png" alt="Carrito">
            <span class="cart-count">0</span>
        </a>
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
                        <h3>Pedido #<?= $pedido['id'] ?></h3>
                        <p><strong>Estado:</strong> <?= ucfirst($pedido['estado']) ?></p>
                        <p><strong>Total:</strong> €<?= number_format($pedido['total'], 2, ',', '.') ?></p>
                        <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?></p>
                    </div>
                    <div class="text-right">
                        <span class="badge-status badge-estado-<?= $pedido['estado'] ?>">
                            <?= ucfirst($pedido['estado']) ?>
                        </span>
                    </div>
                </div>
                <p><strong>Productos:</strong> <?= htmlspecialchars($pedido['items']) ?></p>
                
                <?php if (in_array($pedido['estado'], ['pendiente', 'preparando'])): ?>
                    <button type="button" class="btn-cancelar" onclick="confirmarCancelacion(<?= $pedido['id'] ?>)">
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
profileBtn.addEventListener('click', () => dropdownMenu.classList.toggle('show'));
window.addEventListener('click', e => {
    if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove('show');
    }
});

function confirmarCancelacion(pedidoId) {
    if (confirm('¿Estás seguro de que quieres cancelar este pedido?')) {
        window.location.href = 'mis_pedidos.php?cancelar=1&id=' + pedidoId;
    }
}
</script>
</body>
</html>

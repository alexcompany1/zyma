<?php
/**
 * gestionar_pedidos.php
 * Gestiona pedidos y estados.
 */

session_start();
require_once 'config.php';

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
        // Leer items
        $stmt = $pdo->prepare("
            SELECT producto_id, cantidad 
            FROM pedido_items 
            WHERE pedido_id = ?
        ");
        $stmt->execute([$pedidoId]);
        $items_pedido = $stmt->fetchAll();
        
        // Actualizar stock
        foreach ($items_pedido as $item) {
            $producto_id = $item['producto_id'];
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
        
        // Actualizar estado
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = 'preparando' WHERE id_pedido = ?");
        $stmt->execute([$pedidoId]);
        
        header('Location: gestionar_pedidos.php');
        exit;
        
    } catch (Exception $e) {
        die("<h2 class='page-error'>Error al preparar: " . htmlspecialchars($e->getMessage()) . "</h2>");
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
                        <p><strong>Total:</strong> €<?= number_format($pedido['total'], 2, ',', '.') ?></p>
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
profileBtn.addEventListener('click', () => dropdownMenu.classList.toggle('show'));
window.addEventListener('click', e => {
    if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove('show');
    }
});
</script>
</body>
</html>

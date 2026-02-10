<?php
/**
 * carrito.php
 * Muestra el carrito y crea el pedido.
 */

require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$products = [
    ['id' => 1, 'name' => 'Nachos con Queso', 'price' => 6.00, 'image' => 'assets/nachos.png'],
    ['id' => 2, 'name' => 'Patatas Fritas', 'price' => 3.50, 'image' => 'assets/fries.png'],
    ['id' => 3, 'name' => 'Hotdog BBQ', 'price' => 7.50, 'image' => 'assets/bbq_hotdog.png'],
    ['id' => 4, 'name' => 'Hotdog Clásico', 'price' => 5.99, 'image' => 'assets/hotdog.png'],
    ['id' => 5, 'name' => 'Hotdog Vegano', 'price' => 6.50, 'image' => 'assets/vegan-hotdog.png'],
    ['id' => 6, 'name' => 'Refresco Cola', 'price' => 2.00, 'image' => 'assets/soda.png'],
    ['id' => 7, 'name' => 'Agua Mineral', 'price' => 1.50, 'image' => 'assets/water.png']
];

$cartItems = [];
$total = 0;

foreach ($_SESSION['cart'] ?? [] as $id => $qty) {
    foreach ($products as $product) {
        if ($product['id'] == $id) {
            $product['quantity'] = $qty;
            $product['subtotal'] = $product['price'] * $qty;
            $cartItems[] = $product;
            $total += $product['subtotal'];
        }
    }
}

// Procesar pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['realizar_pedido'])) {
    if (empty($cartItems)) {
        $error = "No hay productos en el carrito.";
    } else {
        try {
            // Crear pedido
            $stmt = $pdo->prepare("
                INSERT INTO pedidos (
                    id_mesa, 
                    id_usuario, 
                    fecha_hora, 
                    estado, 
                    total, 
                    metodo_pago, 
                    notas_cliente
                ) VALUES (?, ?, NOW(), ?, ?, ?, ?)
            ");
            
            // Datos del pedido
            $id_mesa = 0; // ajustar segun tu caso
            $id_usuario = $_SESSION['user_id'];
            $estado = 'pendiente';
            $total_pedido = $total;
            $metodo_pago = 'qr'; // ajustar metodo
            $notas_cliente = ''; // notas del cliente
            
            $stmt->execute([
                $id_mesa,
                $id_usuario, 
                $estado, 
                $total_pedido, 
                $metodo_pago, 
                $notas_cliente
            ]);
            
            $pedidoId = $pdo->lastInsertId();
            
            // Guardar items del pedido
            foreach ($cartItems as $item) {
                // Habilita si usas pedido_items
                /*
                $stmt = $pdo->prepare("
                    INSERT INTO pedido_items (pedido_id, producto_id, cantidad, precio_unitario) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$pedidoId, $item['id'], $item['quantity'], $item['price']]);
                */
            }
            
            // Crear notificacion
            $stmt = $pdo->prepare("INSERT INTO notificaciones (mensaje, tipo, leida) VALUES (?, 'pedido_nuevo', 0)");
            $mensaje = "Nuevo pedido #{$pedidoId} de " . $_SESSION['email'] . " por " . number_format($total, 2, ',', '.') . "€";
            $stmt->execute([$mensaje]);
            
            // Vaciar carrito
            unset($_SESSION['cart']);
            
            // Ir a confirmacion
            $_SESSION['mensaje_pedido'] = "¡Pedido realizado con éxito! Tu pedido #{$pedidoId} está siendo procesado.";
            header("Location: pedido_confirmado.php");
            exit;
            
        } catch (Exception $e) {
            $error = "Error al procesar el pedido: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zyma - Tu Carrito</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<!-- Header -->
<div class="header-container">
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
    
    <div class="header-content">
        <a href="usuario.php" class="logo">Zyma</a>
    </div>
    
    <div class="cart-section">
        <a href="carrito.php" class="cart-btn">
            <img src="assets/cart-icon.png" alt="Carrito">
            <span class="cart-count"><?= count($_SESSION['cart'] ?? []) ?></span>
        </a>
    </div>
</div>

<div class="container">
    <div class="cart-container">
        <div class="cart-header">
            <h1>Tu Carrito</h1>
            <p>Revisa tus productos antes de finalizar el pedido</p>
        </div>

        <?php if (empty($cartItems)): ?>
            <p class="empty-state">Tu carrito está vacío.</p>
            <a href="carta.php" class="btn-seguir-comprando btn-block">Seguir comprando</a>
        <?php else: ?>
            <?php foreach ($cartItems as $item): ?>
            <div class="cart-item-row">
                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-img">
                
                <div class="cart-item-info">
                    <div class="cart-item-name">
                        <?= htmlspecialchars($item['name']) ?>
                    </div>
                    <div class="cart-item-meta" id="desc-<?= $item['id'] ?>">
                        €<?= number_format($item['price'], 2, ',', '.') ?> x <?= $item['quantity'] ?>
                    </div>
                    <div class="cart-item-subtotal" id="subtotal-<?= $item['id'] ?>">
                        €<?= number_format($item['subtotal'], 2, ',', '.') ?>
                    </div>
                </div>
                
                <div class="quantity-controls">
                    <button class="quantity-btn" onclick="changeQuantity(<?= $item['id'] ?>, -1)">−</button>
                    <span class="quantity-value" id="qty-<?= $item['id'] ?>"><?= $item['quantity'] ?></span>
                    <button class="quantity-btn" onclick="changeQuantity(<?= $item['id'] ?>, 1)">+</button>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="center mt-3">
                <div class="cart-total-line" id="total-amount">
                    Total: €<?= number_format($total, 2, ',', '.') ?>
                </div>
                
                <div class="btn-row center">
                    <form method="POST">
                        <button type="submit" name="realizar_pedido" class="btn-realizar-pedido btn-block">
                            Realizar Pedido
                        </button>
                    </form>
                    <a href="carta.php" class="btn-seguir-comprando">
                        Seguir comprando
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
const profileBtn = document.getElementById('profileBtn');
const dropdownMenu = document.getElementById('dropdownMenu');

profileBtn.addEventListener('click', () => {
    dropdownMenu.classList.toggle('show');
});

window.addEventListener('click', (e) => {
    if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove('show');
    }
});

function changeQuantity(productId, delta) {
    const qtyElement = document.getElementById('qty-' + productId);
    const descElement = document.getElementById('desc-' + productId);
    const subtotalElement = document.getElementById('subtotal-' + productId);
    const totalElement = document.getElementById('total-amount');
    
    let currentQty = parseInt(qtyElement.textContent);
    
    if (delta === -1 && currentQty === 1) {
        window.location.href = 'eliminar_producto.php?id=' + productId;
        return;
    }
    
    if (currentQty + delta < 1) return;
    
    currentQty += delta;
    qtyElement.textContent = currentQty;
    
    const prices = [0, 6.00, 3.50, 7.50, 5.99, 6.50, 2.00, 1.50];
    
    descElement.innerHTML = '€' + prices[productId].toFixed(2).replace('.', ',') + ' x ' + currentQty;
    const subtotal = prices[productId] * currentQty;
    subtotalElement.innerHTML = '€' + subtotal.toFixed(2).replace('.', ',');
    
    let total = 0;
    <?php foreach ($cartItems as $item): ?>
        if (<?= $item['id'] ?> === productId) {
            total += <?= $item['price'] ?> * currentQty;
        } else {
            total += <?= $item['price'] ?> * <?= $item['quantity'] ?>;
        }
    <?php endforeach; ?>
    totalElement.innerHTML = 'Total: €' + total.toFixed(2).replace('.', ',');
}
</script>
</body>
</html>



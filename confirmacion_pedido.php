<?php
/**
 * confirmacion_pedido.php
 * Muestra el resumen del pedido.
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config.php'; // Conexion PDO

// Leer id de pedido
$pedido_id = $_GET['id'] ?? null;
if (!$pedido_id) {
    header('Location: usuario.php');
    exit;
}

// Cargar pedido
$stmt = $pdo->prepare("
    SELECT p.id, p.total, p.fecha, u.email
    FROM pedidos p
    JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.id = :pedido_id AND p.usuario_id = :usuario_id
");
$stmt->execute([
    ':pedido_id' => $pedido_id,
    ':usuario_id' => $_SESSION['user_id']
]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    echo "<p class='empty-state'>Pedido no encontrado.</p>";
    exit;
}

// Cargar items
$stmtItems = $pdo->prepare("
    SELECT pi.producto_id, pi.cantidad, pi.precio_unitario, pr.nombre
    FROM pedido_items pi
    JOIN productos pr ON pi.producto_id = pr.id
    WHERE pi.pedido_id = :pedido_id
");
$stmtItems->execute([':pedido_id' => $pedido_id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Pedido - Zyma</title>
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

    <div class="header-content">
        <a href="usuario.php" class="logo">Zyma</a>
    </div>

    <div class="cart-section">
        <a href="carrito.php" class="cart-btn">
            <img src="assets/cart-icon.png" alt="Carrito">
            <span class="cart-count">0</span>
        </a>
    </div>
</header>

<div class="container">
    <div class="main-content">
        <h2 class="welcome">¡Pedido confirmado!</h2>
        <p class="muted mb-3">
            Gracias, <?= htmlspecialchars($pedido['email']) ?>. Tu pedido ha sido registrado exitosamente.
        </p>

        <div class="summary-box">
            <h3 class="summary-title">Resumen del Pedido #<?= $pedido['id'] ?></h3>
            <p>Fecha: <?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?></p>

            <table class="table-compact mt-2">
                <thead>
                    <tr class="table-head-dark">
                        <th>Producto</th>
                        <th class="center">Cantidad</th>
                        <th class="text-right">Precio Unitario</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nombre']) ?></td>
                            <td class="center"><?= $item['cantidad'] ?></td>
                            <td class="text-right">$<?= number_format($item['precio_unitario'],2) ?></td>
                            <td class="text-right">$<?= number_format($item['cantidad'] * $item['precio_unitario'],2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p class="summary-total">
                Total: $<?= number_format($pedido['total'],2) ?>
            </p>
        </div>

        <a href="usuario.php" class="btn-cart">Volver al Inicio</a>
    </div>
</div>

<footer>
    <p>© 2025 Zyma. Todos los derechos reservados.</p>
</footer>

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
</script>
</body>
</html>


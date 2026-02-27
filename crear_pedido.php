<?php
/**
 * crear_pedido.php
 * Crea un pedido desde el carrito.
 */

session_start();

// Validar sesion
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Validar carrito
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: carrito.php'); // volver al carrito
    exit;
}

// Conexion BD
require_once 'config.php'; // incluye PDO

try {
    // Iniciar transaccion
    $pdo->beginTransaction();

    // Calcular total
    $total = 0;
    foreach ($_SESSION['cart'] as $producto_id => $item) {
        $total += $item['precio'] * $item['cantidad'];
    }

    // Calcular manualmente siguiente id_pedido (en caso de tabla sin AUTO_INCREMENT)
    $stmtNext = $pdo->query("SELECT COALESCE(MAX(id_pedido),0) FROM pedidos");
    $nextId = (int)$stmtNext->fetchColumn() + 1;

    // Guardar pedido incluyendo id_pedido
    $stmt = $pdo->prepare(
        "INSERT INTO pedidos (id_pedido, id_usuario, total) VALUES (:pedido_id, :usuario_id, :total)"
    );
    $stmt->execute([
        ':pedido_id' => $nextId,
        ':usuario_id' => $_SESSION['user_id'],
        ':total' => $total
    ]);
    $pedido_id = $nextId;

    // Guardar items
    $stmtItem = $pdo->prepare(
        "INSERT INTO pedido_items (id_pedido, id_producto, cantidad, precio_unitario) VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario)"
    );
    foreach ($_SESSION['cart'] as $producto_id => $item) {
        $stmtItem->execute([
            ':pedido_id' => $pedido_id,
            ':producto_id' => $producto_id,
            ':cantidad' => $item['cantidad'],
            ':precio_unitario' => $item['precio']
        ]);
    }

    // Confirmar transaccion
    $pdo->commit();

    // Vaciar carrito
    unset($_SESSION['cart']);

    // Ir al ticket del pedido
    header("Location: ticket.php?id=$pedido_id");
    exit;

} catch (Exception $e) {
    // Revertir si falla
    $pdo->rollBack();
    echo "<div class='error-card'>";
    echo "<h2 class='center mb-2'>Error al crear pedido</h2>";
    echo "<p class='center'>".htmlspecialchars($e->getMessage())."</p>";
    echo "</div>";
}
?>


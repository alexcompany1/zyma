<?php
/**
 * update_quantity.php
 * Actualiza cantidad y totales.
 */

session_start();
header('Content-Type: application/json');

require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['productId']) && isset($input['quantity'])) {
    $productId = (int) $input['productId'];
    $quantity = (int) $input['quantity'];

    if ($quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Cantidad invalida']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT precio FROM productos WHERE id = ?");
    $stmt->execute([$productId]);
    $precio = $stmt->fetchColumn();

    if ($precio === false) {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        exit;
    }

    $_SESSION['cart'][$productId] = $quantity;

    $subtotal = (float) $precio * $quantity;

    $total = 0.0;
    foreach ($_SESSION['cart'] as $id => $qty) {
        $stmt->execute([(int) $id]);
        $precioProducto = $stmt->fetchColumn();
        if ($precioProducto !== false) {
            $total += (float) $precioProducto * (int) $qty;
        }
    }

    echo json_encode([
        'success' => true,
        'subtotal' => number_format($subtotal, 2, ',', '.'),
        'total' => number_format($total, 2, ',', '.')
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Datos invalidos']);

<?php
/**
 * update_quantity.php
 * Actualiza cantidad y totales.
 */

session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['productId']) && isset($input['quantity'])) {
    $productId = intval($input['productId']);
    $quantity = intval($input['quantity']);
    
    if ($quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Cantidad inválida']);
        exit;
    }
    
    // Lista de precios
    $products = [
        1 => ['price' => 6.00],
        2 => ['price' => 3.50],
        3 => ['price' => 7.50],
        4 => ['price' => 5.99],
        5 => ['price' => 6.50],
        6 => ['price' => 2.00],
        7 => ['price' => 1.50]
    ];
    
    if (!isset($products[$productId])) {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        exit;
    }
    
    // Guardar en sesion
    $_SESSION['cart'][$productId] = $quantity;
    
    // Calcular subtotal
    $subtotal = $products[$productId]['price'] * $quantity;
    
    // Calcular total
    $total = 0;
    foreach ($_SESSION['cart'] as $id => $qty) {
        if (isset($products[$id])) {
            $total += $products[$id]['price'] * $qty;
        }
    }
    
    echo json_encode([
        'success' => true,
        'subtotal' => number_format($subtotal, 2, ',', '.'),
        'total' => number_format($total, 2, ',', '.')
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
}
?>

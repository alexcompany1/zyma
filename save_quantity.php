<?php
/**
 * save_quantity.php
 * Guarda cantidad en sesion.
 */

session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['productId']) && isset($input['quantity'])) {
    $productId = intval($input['productId']);
    $quantity = intval($input['quantity']);
    
    // Validar cantidad
    if ($quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'La cantidad debe ser mayor que 0']);
        exit;
    }
    
    // Guardar en sesion
    $_SESSION['cart'][$productId] = $quantity;
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
}
?>

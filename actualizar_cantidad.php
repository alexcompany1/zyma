<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$index = (int)($input['index'] ?? -1);
$delta = (int)($input['delta'] ?? 0);

if ($index < 0 || !isset($_SESSION['cart'][$index])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Producto no encontrado en el carrito']);
    exit;
}

$currentQty = (int)($_SESSION['cart'][$index]['quantity'] ?? 1);
$newQty = $currentQty + $delta;

if ($newQty < 1) {
    unset($_SESSION['cart'][$index]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    echo json_encode(['success' => true, 'removed' => true]);
    exit;
}

$_SESSION['cart'][$index]['quantity'] = $newQty;

echo json_encode(['success' => true, 'quantity' => $newQty]);

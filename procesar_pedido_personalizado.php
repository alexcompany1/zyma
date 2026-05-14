<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: carta.php');
    exit;
}

$userId = $_SESSION['user_id'];
$productId = (int)($_POST['product_id'] ?? 0);
$quantity = max(1, (int)($_POST['quantity'] ?? 1));
$extras = $_POST['extras'] ?? [];

if ($productId <= 0) {
    $_SESSION['error'] = 'Producto no válido.';
    header('Location: carta.php');
    exit;
}

// $pdo ya está disponible desde config.php

// Obtener producto
$stmt = $pdo->prepare("SELECT id, nombre, precio FROM productos WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['error'] = 'Producto no encontrado.';
    header('Location: carta.php');
    exit;
}

// Calcular precio con extras
$basePrice = (float)$product['precio'];
$extrasTotal = 0;
$extrasDetails = [];

if (!empty($extras)) {
    try {
        $placeholders = implode(',', array_fill(0, count($extras), '?'));
        $stmt = $pdo->prepare("SELECT id, nombre, precio_adicional FROM product_extras WHERE id IN ($placeholders) AND activo = 1");
        $stmt->execute($extras);
        $selectedExtras = $stmt->fetchAll();

        foreach ($selectedExtras as $extra) {
            $extrasTotal += (float)$extra['precio_adicional'];
            $extrasDetails[] = [
                'id'    => (int)$extra['id'],
                'name'  => $extra['nombre'],
                'price' => (float)$extra['precio_adicional'],
            ];
        }
    } catch (Throwable $e) {
        $extrasTotal = 0;
        $extrasDetails = [];
    }
}

$finalPrice = ($basePrice + $extrasTotal) * $quantity;

// Añadir al carrito
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cartItem = [
    'id' => $productId,
    'name' => $product['nombre'],
    'price' => $basePrice,
    'quantity' => $quantity,
    'extras' => $extrasDetails,
    'final_price' => $finalPrice
];

$_SESSION['cart'][] = $cartItem;

$_SESSION['toast_message'] = [
    'text' => 'Producto personalizado añadido al carrito',
    'icon' => 'OK'
];

header('Location: carta.php');
exit;

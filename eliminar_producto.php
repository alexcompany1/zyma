<?php
/**
 * eliminar_producto.php
 * Quita un producto del carrito (por índice).
 */

// Iniciar Sesión
session_start();

// Validar Sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Leer índice del producto
$index = null;
if (isset($_GET['index'])) {
    $index = (int)$_GET['index'];
} elseif (isset($_GET['id'])) {
    // Compatibilidad hacia atrás: buscar por ID
    $productId = (int)$_GET['id'];
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $idx => $item) {
            if (isset($item['id']) && $item['id'] === $productId) {
                $index = $idx;
                break;
            }
        }
    }
}

// Borrar del carrito
if ($index !== null && isset($_SESSION['cart'][$index])) {
    unset($_SESSION['cart'][$index]);
    // Reindexar el array
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}

// Volver al carrito
header('Location: carrito.php');
exit;
?>


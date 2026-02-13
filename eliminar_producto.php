<?php
/**
 * eliminar_producto.php
 * Quita un producto del carrito.
 */

// Iniciar sesion
session_start();

// Validar sesion
if (!isset($_SESSION['user_id'])) {
    // Ir a login
    header('Location: login.php');
    exit;
}

// Leer id de producto
if (isset($_GET['id'])) {
    $productId = intval($_GET['id']);
    
    // Borrar del carrito
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
    
    // Volver al carrito
    header('Location: carrito.php');
    exit;
} else {
    // Sin id, volver
    header('Location: carrito.php');
    exit;
}
?>

<?php
/**
 * editar_carta.php
 * tonto
 * Panel para editar productos.
 */

session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['worker_code']) {
    die("Acceso denegado.");
}

// Cargar productos
$stmt = $pdo->query("SELECT * FROM productos ORDER BY nombre");
$productos = $stmt->fetchAll();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['editar'])) {
        $id = intval($_POST['id']);
        $nombre = trim($_POST['nombre']);
        $precio = floatval($_POST['precio']);
        $imagen = trim($_POST['imagen']);
        
        $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, precio = ?, imagen = ? WHERE id = ?");
        $stmt->execute([$nombre, $precio, $imagen, $id]);
        
        $_SESSION['mensaje'] = "Producto actualizado correctamente.";
        header('Location: editar_carta.php');
        exit;
    } elseif (isset($_POST['eliminar'])) {
        $id = intval($_POST['id']);
        
        // Borrar relaciones
        $stmt = $pdo->prepare("DELETE FROM producto_ingredientes WHERE producto_id = ?");
        $stmt->execute([$id]);
        
        // Borrar producto
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['mensaje'] = "Producto eliminado correctamente.";
        header('Location: editar_carta.php');
        exit;
    } elseif (isset($_POST['nuevo'])) {
        $nombre = trim($_POST['nombre']);
        $precio = floatval($_POST['precio']);
        $imagen = trim($_POST['imagen']);
        
        $stmt = $pdo->prepare("INSERT INTO productos (nombre, precio, imagen) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $precio, $imagen]);
        
        $_SESSION['mensaje'] = "Producto añadido correctamente.";
        header('Location: editar_carta.php');
        exit;
    }
}

$mensaje = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zyma - Editar Carta</title>
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
    <div class="header-content"><a href="usuario.php" class="logo">Zyma</a></div>
    <div class="cart-section">
        <a href="carrito.php" class="cart-btn">
            <img src="assets/cart-icon.png" alt="Carrito">
            <span class="cart-count">0</span>
        </a>
    </div>
</header>

<div class="container">
    <div class="main-content">
        <h2 class="welcome">Editar Carta</h2>
        
        <!-- Boton volver -->
        <a href="trabajador.php" class="btn-volver-panel">Volver al Panel de Control</a>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-success alert-spaced"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        
        <!-- Nuevo producto -->
        <div class="form-container">
            <h3>Añadir Nuevo Producto</h3>
            <form method="POST" class="product-form">
                <input type="text" name="nombre" placeholder="Nombre del producto" required>
                <input type="number" name="precio" step="0.01" placeholder="Precio" required>
                <input type="text" name="imagen" placeholder="Ruta de la imagen (ej: assets/nachos.png)" required>
                <button type="submit" name="nuevo" class="btn-add-cart">Añadir Producto</button>
            </form>
        </div>
        
        <!-- Productos existentes -->
        <div class="form-container">
            <h3>Productos Existentes</h3>
            <?php foreach ($productos as $producto): ?>
            <form method="POST" class="product-form form-divider">
                <input type="hidden" name="id" value="<?= $producto['id'] ?>">
                <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required>
                <input type="number" name="precio" step="0.01" value="<?= $producto['precio'] ?>" required>
                <input type="text" name="imagen" value="<?= htmlspecialchars($producto['imagen']) ?>" required>
                <div class="form-actions">
                    <button type="submit" name="editar" class="btn-add-cart flex-1">Actualizar</button>
                    <button type="submit" name="eliminar" class="remove-item-btn flex-1">Eliminar</button>
                </div>
            </form>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
const profileBtn = document.getElementById('profileBtn');
const dropdownMenu = document.getElementById('dropdownMenu');
profileBtn.addEventListener('click', () => dropdownMenu.classList.toggle('show'));
window.addEventListener('click', e => {
    if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove('show');
    }
});
</script>
</body>
</html>

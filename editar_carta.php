<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

session_start();
require_once 'config.php';
require_once 'auth.php';

zymaRequireRole('worker');

$stmt = $pdo->query("SELECT * FROM productos ORDER BY nombre");
$productos = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['editar'])) {
        $id = (int) ($_POST['id'] ?? 0);
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $precio = (float) ($_POST['precio'] ?? 0);
        $imagen = trim((string) ($_POST['imagen'] ?? ''));

        $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, precio = ?, imagen = ? WHERE id = ?");
        $stmt->execute([$nombre, $precio, $imagen, $id]);

        $_SESSION['mensaje'] = 'Producto actualizado correctamente.';
        header('Location: editar_carta.php');
        exit;
    }

    if (isset($_POST['eliminar'])) {
        $id = (int) ($_POST['id'] ?? 0);

        $stmt = $pdo->prepare("DELETE FROM valoraciones WHERE id_producto = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM producto_ingredientes WHERE producto_id = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['mensaje'] = 'Producto eliminado correctamente.';
        header('Location: editar_carta.php');
        exit;
    }

    if (isset($_POST['nuevo'])) {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $precio = (float) ($_POST['precio'] ?? 0);
        $imagen = trim((string) ($_POST['imagen'] ?? ''));

        $stmt = $pdo->prepare("INSERT INTO productos (nombre, precio, imagen) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $precio, $imagen]);

        $_SESSION['mensaje'] = 'Producto añadido correctamente.';
        header('Location: editar_carta.php');
        exit;
    }
}

$mensaje = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);

$display_name = trim($_SESSION['nombre'] ?? '');
if ($display_name === '') {
    $display_name = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? '');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zyma - Editar Carta</title>
<link rel="icon" type="image/png" href="assets/favicon.png">
<link rel="stylesheet" href="styles.css?v=20260428-1">
</head>
<body>
<header class="landing-header">
  <div class="landing-bar">
    <div class="profile-section">
      <button class="profile-btn" id="profileBtn" aria-label="Perfil">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="white">
          <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c1.52 0 5.1 1.34 5.1 5v1H6.9v-1c0-3.66 3.58-5 5.1-5z"/>
        </svg>
      </button>
      <span class="user-name"><?= htmlspecialchars($display_name) ?></span>
      <div class="dropdown" id="dropdownMenu">
        <a href="perfil.php">Mi perfil</a>
        <a href="politica_cookies.php" class="open-cookie-preferences">Personalizar cookies</a>
        <a href="logout.php">Cerrar sesión</a>
      </div>
    </div>

    <a href="trabajador.php" class="landing-logo">
      <span class="landing-logo-text">Zyma</span>
    </a>

    <div class="quick-menu-section">
      <button class="quick-menu-btn" id="quickMenuBtn" aria-label="Menú rápido"></button>
      <div class="dropdown quick-dropdown" id="quickDropdown">
        <a href="trabajador.php">Panel</a>
        <a href="gestionar_pedidos.php">Pedidos</a>
        <a href="editar_carta.php">Cambiar precios</a>
        <a href="estadisticas.php">Estadísticas</a>
      </div>
    </div>

    <div class="landing-actions">
      <a href="trabajador.php" class="landing-link">Panel</a>
      <a href="gestionar_pedidos.php" class="landing-cta">Pedidos</a>
    </div>
  </div>
</header>

<div class="container">
  <div class="main-content">
    <h2 class="welcome">Cambiar precios y productos</h2>
    <a href="trabajador.php" class="btn-volver-panel">Volver al panel de trabajador</a>

    <?php if ($mensaje !== ''): ?>
      <div class="alert alert-success alert-spaced"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="form-container">
      <h3>Cambiar precios de productos</h3>
      <p>Cambia el precio, pulsa guardar y ese importe será el que se use en la carta y en la compra.</p>
    </div>

    <div class="form-container">
      <h3>Añadir nuevo producto</h3>
      <form method="POST" class="product-form">
        <label for="nuevo-nombre">Nombre del producto</label>
        <input id="nuevo-nombre" type="text" name="nombre" placeholder="Nombre del producto" required>

        <label for="nuevo-precio">Precio en EUR</label>
        <input id="nuevo-precio" type="number" name="precio" step="0.01" min="0" placeholder="Precio" required>

        <label for="nueva-imagen">Ruta de la imagen</label>
        <input id="nueva-imagen" type="text" name="imagen" placeholder="Ruta de la imagen (ej: assets/nachos.png)" required>

        <button type="submit" name="nuevo" class="btn-add-cart">Crear producto</button>
      </form>
    </div>

    <div class="form-container">
      <h3>Productos existentes</h3>
      <?php foreach ($productos as $producto): ?>
        <form method="POST" class="product-form form-divider">
          <input type="hidden" name="id" value="<?= (int) $producto['id'] ?>">

          <label for="nombre-<?= (int) $producto['id'] ?>">Nombre del producto</label>
          <input id="nombre-<?= (int) $producto['id'] ?>" type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required>

          <label for="precio-<?= (int) $producto['id'] ?>">Precio actual en EUR</label>
          <input id="precio-<?= (int) $producto['id'] ?>" type="number" name="precio" step="0.01" min="0" value="<?= htmlspecialchars((string) $producto['precio']) ?>" required>

          <label for="imagen-<?= (int) $producto['id'] ?>">Ruta de la imagen</label>
          <input id="imagen-<?= (int) $producto['id'] ?>" type="text" name="imagen" value="<?= htmlspecialchars($producto['imagen']) ?>" required>

          <div class="form-actions">
            <button type="submit" name="editar" class="btn-add-cart flex-1">Guardar precio y cambios</button>
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

if (profileBtn && dropdownMenu) {
  profileBtn.addEventListener('click', () => dropdownMenu.classList.toggle('show'));
  window.addEventListener('click', (e) => {
    if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
      dropdownMenu.classList.remove('show');
    }
  });
}
</script>
<script src="assets/mobile-header.js?v=20260211-6"></script>
<footer>
  <p>&copy; 2025 Zyma. Todos los derechos reservados.</p>
  <p class="footer-legal-links">
    <a href="politica_cookies.php">Política de Cookies</a>
    <span>|</span>
    <a href="politica_privacidad.php">Política de Privacidad</a>
    <span>|</span>
    <a href="aviso_legal.php">Aviso Legal</a>
  </p>
</footer>
</body>
</html>

<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

// menu.php
// Muestra productos y permite pedir con QR
session_start();

require_once 'config.php';

function sanitize($data)
{
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

function tieneAccesoQR()
{
    // Se guarda en sesión
    return isset($_SESSION['qr_validado']) && $_SESSION['qr_validado'] === true;
}

// En config.php ya existen estas variables para PDO.
$conn = new mysqli($host, $username, $password, $dbname, (int)$port);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die('Conexión fallida: ' . $conn->connect_error);
}

$sql_categorias = "SELECT id, nombre FROM categorias_producto ORDER BY nombre";
$result_categorias = $conn->query($sql_categorias);

$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;

if ($categoria_id > 0) {
    $stmt = $conn->prepare('SELECT id, nombre, descripcion, precio, imagen, disponible FROM productos WHERE categoria_id = ? AND disponible = 1 ORDER BY nombre');
    $stmt->bind_param('i', $categoria_id);
} else {
    $stmt = $conn->prepare('SELECT id, nombre, descripcion, precio, imagen, disponible FROM productos WHERE disponible = 1 ORDER BY nombre');
}

$stmt->execute();
$result_productos = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Menú - Restaurante</title>
    <link rel="icon" type="image/png" href="assets/favicon.png">
    <link rel="stylesheet" href="styles.css?v=20260211-5" />
    <script src="script.js" defer></script> <!-- JS del carrito -->
</head>
<body>

    <header class="landing-header">
      <div class="landing-bar">
        <a href="index.php" class="landing-logo">
          <span class="landing-logo-text">Zyma</span>
        </a>
        <div class="landing-actions">
          <a href="login.php" class="landing-link">Entrar</a>
          <a href="registro.php" class="landing-cta">Crear cuenta</a>
        </div>
      </div>
    </header>

    <nav>
        <h2>Categorías</h2>
        <ul>
            <li><a href="menú.php" <?php if ($categoria_id == 0) echo 'class="activo"'; ?>>Todas</a></li>
            <?php while($cat = $result_categorias->fetch_assoc()): ?>
                <li>
                    <a href="menú.php?categoria=<?php echo $cat['id']; ?>" <?php if ($categoria_id == $cat['id']) echo 'class="activo"'; ?>>
                        <?php echo sanitize($cat['nombre']); ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </nav>

    <section id="productos">
        <?php if ($result_productos->num_rows > 0): ?>
            <?php while ($producto = $result_productos->fetch_assoc()): ?>
                <article class="producto">
                    <h3><?php echo sanitize($producto['nombre']); ?></h3>
                    <?php if (!empty($producto['imagen'])): ?>
                        <img src="fotos/<?php echo sanitize($producto['imagen']); ?>" alt="Foto de <?php echo sanitize($producto['nombre']); ?>" class="foto-producto" />
                    <?php endif; ?>
                    <p><?php echo nl2br(sanitize($producto['descripcion'])); ?></p>
                    <p><strong>Precio:</strong> <?php echo number_format((float)$producto['precio'], 2, ',', '.') . ' EUR'; ?></p>

                    <?php if (tieneAccesoQR()): ?>
                        <!-- Boton agregar -->
                        <button class="btn-agregar"
                            data-id="<?php echo $producto['id']; ?>"
                            data-nombre="<?php echo sanitize($producto['nombre']); ?>"
                            data-precio="<?php echo $producto['precio']; ?>">
                            Añadir al carrito
                        </button>
                    <?php else: ?>
                        <p><em>Accede vía QR para hacer pedidos</em></p>
                    <?php endif; ?>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No hay productos disponibles en esta categoría.</p>
        <?php endif; ?>
    </section>

    <aside id="carrito-container" class="hidden">
        <h2>Carrito de Pedidos</h2>
        <div id="carrito-lista"></div>
        <button id="btn-enviar-pedido">Enviar pedido</button>
    </aside>

<script src="assets/mobile-header.js?v=20260211-6"></script>
<script src="assets/language-switcher.js?v=20260413-1"></script>
<footer>
  <p>&copy; 2025 Zyma. Todos los derechos reservados.</p>
  <p class="footer-legal-links">
    <a href="politica_cookies.php">Política de Cookies</a>
    <span>|</span>
    <a href="politica_privacidad.php">Política de Privacidad</a>
    <span>|</span>
    <a href="aviso_legal.php">Aviso Legal</a>
  </p></footer>
</body>
</html>

<?php
// Cerrar conexión
$stmt->close();
$conn->close();
?>



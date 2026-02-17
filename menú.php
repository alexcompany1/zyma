<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">

<?php
// menu.php
// Muestra productos y permite pedir con QR
session_start();

// Configuracion BD
require 'config.php';

// Sanitizar texto
function sanitize($data) {
    return htmlspecialchars(trim($data));}

// Validar acceso QR
function tieneAccesoQR() {
    // Se guarda en sesion
    return isset($_SESSION['qr_validado']) && $_SESSION['qr_validado'] === true;}

// Conexion mysqli
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Validar conexion
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);}

// Cargar categorias
$sql_categorias_producto = "SELECT id, nombre FROM categorias_producto ORDER BY nombre";
$result_categorias_producto = $conn->query($sql_categorias_producto);

// Leer filtro
$categoria_id = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;

// Consulta productos
if ($categoria_id > 0) {
    $stmt = $conn->prepare("SELECT id, nombre, descripcion, precio, imagen, disponible FROM productos WHERE categoria_id = ? AND disponible=1 ORDER BY nombre");
    $stmt->bind_param("i", $categoria_id);
} else {
    $stmt = $conn->prepare("SELECT id, nombre, descripcion, precio, imagen, disponible FROM productos WHERE disponible=1 ORDER BY nombre");
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
                    <p><strong>Precio:</strong> <?php echo number_format($producto['precio'], 2, ',', '.') . ' ?'; ?></p>

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
</body>
</html>

<?php
// Cerrar conexion
$stmt->close();
$conn->close();
?>

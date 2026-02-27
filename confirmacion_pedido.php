<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
?>
<?php
/**
 * confirmacion_pedido.php
 * Muestra el resumen del pedido.
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config.php'; // Conexion PDO

// Leer id de pedido
$pedido_id = $_GET['id'] ?? null;
if (!$pedido_id) {
    header('Location: usuario.php');
    exit;
}

// Cargar pedido
$stmt = $pdo->prepare("
    SELECT p.id_pedido, p.total, p.fecha_hora, u.email
    FROM pedidos p
    JOIN usuarios u ON p.id_usuario = u.id
    WHERE p.id_pedido = :pedido_id AND p.id_usuario = :usuario_id
");
$stmt->execute([
    ':pedido_id' => $pedido_id,
    ':usuario_id' => $_SESSION['user_id']
]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    echo "<p class='empty-state'>Pedido no encontrado.</p>";
    exit;
}

// Cargar items
$stmtItems = $pdo->prepare("
    SELECT pi.id_producto, pi.cantidad, pi.precio_unitario, pr.nombre
    FROM pedido_items pi
    JOIN productos pr ON pi.id_producto = pr.id
    WHERE pi.id_pedido = :pedido_id
");
$stmtItems->execute([':pedido_id' => $pedido_id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Pedido - Zyma</title>
    <link rel="stylesheet" href="styles.css?v=20260211-5">
</head>
<body>
    
<?php
  $display_name = trim($_SESSION['nombre'] ?? '');
  if ($display_name === '') {
    $display_name = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? '');
  }
?>
<header class="landing-header">
  <div class="landing-bar">
    <div class="profile-section">
      <button class="profile-btn" id="profileBtn">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="white">
          <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4 1.79 4 4 4zm0 2c1.52 0 5.1 1.34 5.1 5v1H6.9v-1c0-3.66 3.58-5 5.1-5z"/>
        </svg>
      </button>
      <span class="user-name"><?= htmlspecialchars($display_name) ?></span>
      <div class="dropdown" id="dropdownMenu">
          <a href="perfil.php">Mi perfil</a>
          <a href="logout.php">Cerrar sesion</a>
        </div>
    </div>

    <a href="usuario.php" class="landing-logo">
      <span class="landing-logo-text">Zyma</span>
    </a>

        <div class="quick-menu-section">
      <button class="quick-menu-btn" id="quickMenuBtn" aria-label="Menu rapido"></button>
      <div class="dropdown quick-dropdown" id="quickDropdown">
        <a href="usuario.php">Inicio</a>
        <a href="carta.php">Ver carta</a>
                <a href="tickets.php">Tickets</a>
      </div>
    </div>
    <div class="cart-section">
      <a href="carrito.php" class="cart-btn">
        <img src="assets/cart-icon.png" alt="Carrito">
        <span class="cart-count"><?= count($_SESSION['cart'] ?? []) ?></span>
      </a>
    </div>
  </div>
</header>

<div class="container">
    <div class="main-content">
        <h2 class="welcome">¡Pedido confirmado!</h2>
        <p class="muted mb-3">
            Gracias, <?= htmlspecialchars($pedido['email']) ?>. Tu pedido ha sido registrado exitosamente.
        </p>

        <div class="summary-box">
            <h3 class="summary-title">Resumen del Pedido #<?= $pedido['id_pedido'] ?></h3>
            <p>Fecha: <?= date('d/m/Y H:i', strtotime($pedido['fecha_hora'])) ?></p>

            <table class="table-compact mt-2">
                <thead>
                    <tr class="table-head-dark">
                        <th>Producto</th>
                        <th class="center">Cantidad</th>
                        <th class="text-right">Precio Unitario</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nombre']) ?></td>
                            <td class="center"><?= $item['cantidad'] ?></td>
                            <td class="text-right">$<?= number_format($item['precio_unitario'],2) ?></td>
                            <td class="text-right">$<?= number_format($item['cantidad'] * $item['precio_unitario'],2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p class="summary-total">
                Total: $<?= number_format($pedido['total'],2) ?>
            </p>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:16px;">
            <a href="ticket.php?id=<?= $pedido['id_pedido'] ?>" class="btn-cart">Ver Ticket</a>
            <a href="tickets.php" class="btn-cart">Todos mis Tickets</a>
            <a href="usuario.php" class="btn-cart">Volver al Inicio</a>
        </div>
    </div>
</div>

<footer>
    <p>© 2025 Zyma. Todos los derechos reservados.</p>
</footer>

<script>
    const profileBtn = document.getElementById('profileBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');

    profileBtn.addEventListener('click', () => {
        dropdownMenu.classList.toggle('show');
    });

    window.addEventListener('click', (e) => {
        if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.remove('show');
        }
    });
</script>
<script src="assets/mobile-header.js?v=20260211-6"></script>
</body>
</html>


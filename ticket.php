<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'config.php';

$pedido_id = $_GET['id'] ?? null;
if (!$pedido_id) {
    header('Location: tickets.php');
    exit;
}

// Cargar pedido
$stmt = $pdo->prepare(
    "SELECT p.id_pedido, p.total, p.fecha_hora, u.email FROM pedidos p JOIN usuarios u ON p.id_usuario = u.id WHERE p.id_pedido = :pedido_id AND p.id_usuario = :usuario_id"
);
$stmt->execute([':pedido_id' => $pedido_id, ':usuario_id' => $_SESSION['user_id']]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$pedido) {
    echo "<p class='empty-state'>Ticket no encontrado.</p>";
    exit;
}
$pedido_num = $pedido['id_pedido'];

// Cargar items
$stmtItems = $pdo->prepare(
    "SELECT pi.id_producto, pi.cantidad, pi.precio_unitario, pr.nombre FROM pedido_items pi JOIN productos pr ON pi.id_producto = pr.id WHERE pi.id_pedido = :pedido_id"
);
$stmtItems->execute([':pedido_id' => $pedido_id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?= htmlspecialchars($pedido_num) ?> - Zyma</title>
    <link rel="stylesheet" href="styles.css?v=20260211-5">
    <style>
        .ticket-box{max-width:720px;margin:16px auto;padding:20px;border-radius:8px;background:#fff;box-shadow:0 6px 18px rgba(0,0,0,0.06)}
        .ticket-header{display:flex;justify-content:space-between;align-items:center}
        .ticket-items table{width:100%;border-collapse:collapse}
        .ticket-items th,.ticket-items td{padding:8px;border-bottom:1px solid #eee}
        .ticket-actions{display:flex;gap:8px;margin-top:12px}
    </style>
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
        <a href="valoraciones.php">Valoraciones</a>
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
        <div class="ticket-box">
            <div class="ticket-header">
                <div>
                    <h3>Ticket de compra</h3>
                    <p class="muted">Pedido #<?= htmlspecialchars($pedido_num) ?></p>
                </div>
                <div class="muted">
                    <p><?= date('d/m/Y H:i', strtotime($pedido['fecha_hora'])) ?></p>
                    <p><?= htmlspecialchars($pedido['email']) ?></p>
                </div>
            </div>

            <div class="ticket-items mt-2">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="center">Cantidad</th>
                            <th class="text-right">Precio unidad</th>
                            <th class="text-right">IVA %</th>
                            <th class="text-right">IVA importe</th>
                            <th class="text-right">Subtotal (sin IVA)</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            // Definir tasa de IVA (20% por defecto). Cambia si necesitas otro valor.
                            $tax_rate = defined('TAX_RATE') ? TAX_RATE : 0.20;
                            $subtotal = 0.0;
                            $total_tax = 0.0;
                        ?>
                        <?php foreach ($items as $it): 
                            $cantidad = (int)$it['cantidad'];
                            $precio_unit = (float)$it['precio_unitario'];
                            $line_sub = $precio_unit * $cantidad;
                            $line_tax = $line_sub * $tax_rate;
                            $line_total = $line_sub + $line_tax;
                            $subtotal += $line_sub;
                            $total_tax += $line_tax;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($it['nombre']) ?></td>
                                <td class="center"><?= $cantidad ?></td>
                                <td class="text-right">$<?= number_format($precio_unit,2) ?></td>
                                <td class="text-right"><?= ($tax_rate * 100) ?>%</td>
                                <td class="text-right">$<?= number_format($line_tax,2) ?></td>
                                <td class="text-right">$<?= number_format($line_sub,2) ?></td>
                                <td class="text-right">$<?= number_format($line_total,2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php
                // Totales calculados (nota: en la BD `p.total` se guardó sin IVA)
                $total_with_tax = $subtotal + $total_tax;
            ?>

            <div style="margin-top:12px;text-align:right;">
                <p class="muted">Subtotal: $<?= number_format($subtotal,2) ?></p>
                <p class="muted">IVA: $<?= number_format($total_tax,2) ?></p>
                <p class="summary-total" style="font-weight:700;">Total (con IVA): $<?= number_format($total_with_tax,2) ?></p>
            </div>


            <div class="ticket-actions">
                <a class="btn-cart" href="tickets.php">Volver a Tickets</a>
            </div>
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

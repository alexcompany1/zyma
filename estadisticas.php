<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
?>
<?php
/**
 * estadisticas.php
 * Datos de inventario y pedidos.
 */

session_start();
require_once 'config.php';

// Validar acceso trabajador
if (!isset($_SESSION['user_id']) || !$_SESSION['worker_code']) {
    die("<h2 class='page-error'>Acceso denegado. Solo trabajadores.</h2>");
}

// Cargar ingredientes
try {
    $stmt = $pdo->query("
        SELECT 
            nombre,
            cantidad,
            unidad,
            stock_minimo
        FROM ingredientes 
        GROUP BY nombre
        ORDER BY nombre ASC
    ");
    $ingredientes = $stmt->fetchAll();
} catch (Exception $e) {
    $ingredientes = [];
}

// Contar pendientes
$stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'");
$pedidosPendientes = $stmt->fetchColumn();

// Contar total
$stmt = $pdo->query("SELECT COUNT(*) FROM pedidos");
$pedidosTotales = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zyma - Estadísticas</title>
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
        <h2 class="welcome">Estadísticas - Trabajador</h2>
        
        <!-- Boton volver -->
        <a href="trabajador.php" class="btn-volver-panel">Volver al Panel de Control</a>
        
        <!-- Resumen -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Pedidos Pendientes</h3>
                <p class="stat-number"><?= $pedidosPendientes ?></p>
            </div>
            <div class="stat-card">
                <h3>Pedidos Totales</h3>
                <p class="stat-number"><?= $pedidosTotales ?></p>
            </div>
        </div>

        <!-- Ingredientes -->
        <div class="summary-box">
            <h3 class="summary-title">Inventario de Ingredientes (únicos)</h3>
            
            <?php if (!empty($ingredientes)): ?>
            <table class="ingredient-table">
                <thead>
                    <tr>
                        <th>Ingrediente</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Stock Mínimo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ingredientes as $ingrediente): ?>
                    <?php
                    $lowStock = $ingrediente['cantidad'] <= $ingrediente['stock_minimo'];
                    $class = $lowStock ? 'low-stock' : '';
                    $estado = $lowStock ? 'Bajo stock' : 'Normal';
                    ?>
                    <tr class="<?= $class ?>">
                        <td><?= htmlspecialchars($ingrediente['nombre']) ?></td>
                        <td><?= number_format($ingrediente['cantidad'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($ingrediente['unidad']) ?></td>
                        <td><?= number_format($ingrediente['stock_minimo'], 0, ',', '.') ?></td>
                        <td><?= $estado ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="empty-state">
                    No hay ingredientes registrados en la base de datos.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const profileBtn = document.getElementById('profileBtn');
const dropdownMenu = document.getElementById('dropdownMenu');
const quickDropdown = document.getElementById('quickDropdown');
profileBtn.addEventListener('click', () => dropdownMenu.classList.toggle('show'));
window.addEventListener('click', e => {
    if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove('show');
    }
});

const AUTO_REFRESH_MS = 800;
setInterval(() => {
    if (document.hidden) return;
    if (dropdownMenu && dropdownMenu.classList.contains('show')) return;
    if (quickDropdown && quickDropdown.classList.contains('show')) return;
    window.location.reload();
}, AUTO_REFRESH_MS);
</script>
<script src="assets/mobile-header.js?v=20260211-6"></script>
</body>
</html>


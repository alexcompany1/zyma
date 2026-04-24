<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

session_start();
require_once 'config.php';
require_once 'admin_helpers.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['worker_code'] ?? '') !== 'ADMIN') {
    die("<h2 class='page-error'>Acceso denegado. Solo administradores.</h2>");
}

createAdminSchema($pdo);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $ingredientId = intval($_POST['ingredient_id'] ?? 0);
    if ($ingredientId > 0 && hasTableColumn($pdo, 'ingredientes', 'cantidad')) {
        if ($_POST['action'] === 'update_stock') {
            $change = floatval($_POST['change'] ?? 0);
            $stmt = $pdo->prepare("UPDATE ingredientes SET cantidad = GREATEST(0, cantidad + :delta) WHERE id = :id");
            $stmt->execute([':delta' => $change, ':id' => $ingredientId]);
            $message = 'Stock actualizado.';
        }
        if ($_POST['action'] === 'edit_threshold' && hasTableColumn($pdo, 'ingredientes', 'stock_minimo')) {
            $min = max(0, floatval($_POST['min_quantity'] ?? 0));
            $stmt = $pdo->prepare("UPDATE ingredientes SET stock_minimo = :min WHERE id = :id");
            $stmt->execute([':min' => $min, ':id' => $ingredientId]);
            $message = 'Umbral actualizado.';
        }
    }
}

try {
    $stmt = $pdo->query(
        "SELECT id, nombre, cantidad, unidad AS unit, stock_minimo, COALESCE(unit, 'unidades') AS safe_unit, COALESCE(stock_minimo, 1) AS safe_min
         FROM ingredientes
         ORDER BY nombre ASC"
    );
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $ingredients = [];
    $message = 'Error al cargar ingredientes: ' . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Inventario</title>
<link rel="icon" type="image/png" href="assets/favicon.png">
<link rel="stylesheet" href="styles.css?v=20260211-5">
<style>
.stock-badge { display:inline-block; min-width:72px; padding:.35rem .7rem; border-radius:999px; color:#fff; font-size:.85rem; }
.stock-verde { background:#2ecc71; }
.stock-amarillo { background:#f1c40f; color:#222; }
.stock-rojo { background:#e74c3c; }
.stock-gris { background:#95a5a6; }
.inventory-table { width:100%; border-collapse: collapse; }
.inventory-table th, .inventory-table td { padding:.85rem .75rem; border:1px solid #e7e7e7; text-align:left; }
.inventory-table th { background:#f9f9f9; }
.inventory-actions { display:flex; gap:.6rem; flex-wrap:wrap; }
small { color:#555; }
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
          <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c1.52 0 5.1 1.34 5.1 5v1H6.9v-1c0-3.66 3.58-5 5.1-5z"/>
        </svg>
      </button>
      <span class="user-name"><?= htmlspecialchars($display_name) ?></span>
      <div class="dropdown" id="dropdownMenu">
          <a href="perfil.php">Mi perfil</a>
          <a href="politica_cookies.php" class="open-cookie-preferences">Personalizar cookies</a>
          <a href="logout.php">Cerrar Sesión</a>
      </div>
    </div>
    <a href="admin.php" class="landing-logo"><span class="landing-logo-text">Zyma</span></a>
        <div class="quick-menu-section">
      <button class="quick-menu-btn" id="quickMenuBtn" aria-label="Menú rápido"></button>
      <div class="dropdown quick-dropdown" id="quickDropdown">
        <a href="admin.php">Panel Admin</a>
        <a href="admin_orders.php">Pedidos</a>
        <a href="admin_inventory.php">Inventario</a>
        <a href="admin_products.php">Productos</a>
      </div>
    </div>
  </div>
</header>

<div class="container">
    <div class="section-card">
        <div class="row-between section-head">
            <div>
                <h2>Inventario de ingredientes</h2>
                <p class="lead">Visualiza stock, umbrales y actualiza cantidades al instante.</p>
            </div>
            <div>
                <a href="usuario.php" class="landing-link">Volver a inicio</a>
            </div>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if (empty($ingredients)): ?>
            <p class="empty-state">No hay ingredientes registrados.</p>
        <?php else: ?>
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Ingrediente</th>
                        <th>Stock</th>
                        <th>Umbral</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ingredients as $ingredient): ?>
                        <?php
                            $quantity = (float)$ingredient['cantidad'];
                            $minQuantity = (float)$ingredient['safe_min'];
                            $status = Ingredient::stockStatus($quantity, $minQuantity);
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($ingredient['nombre']) ?></strong>
                                <div><small><?= htmlspecialchars($ingredient['safe_unit']) ?></small></div>
                            </td>
                            <td><?= $quantity ?></td>
                            <td><?= $minQuantity ?></td>
                            <td><span class="stock-badge stock-<?= htmlspecialchars($status) ?>"><?= Ingredient::stockLabel($status) ?></span></td>
                            <td>
                                <div class="inventory-actions">
                                    <form method="POST" style="display:inline-flex; gap:.4rem; align-items:center;">
                                        <input type="hidden" name="action" value="update_stock">
                                        <input type="hidden" name="ingredient_id" value="<?= (int)$ingredient['id'] ?>">
                                        <input type="number" step="0.1" name="change" placeholder="+/-" style="width:80px;" required>
                                        <button type="submit" class="btn-add-cart">Actualizar</button>
                                    </form>
                                    <form method="POST" style="display:inline-flex; gap:.4rem; align-items:center;">
                                        <input type="hidden" name="action" value="edit_threshold">
                                        <input type="hidden" name="ingredient_id" value="<?= (int)$ingredient['id'] ?>">
                                        <input type="number" step="0.1" name="min_quantity" placeholder="Umbral" style="width:80px;" value="<?= htmlspecialchars($minQuantity) ?>" required>
                                        <button type="submit" class="btn-add-cart">Guardar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
const profileBtn = document.getElementById('profileBtn');
const dropdownMenu = document.getElementById('dropdownMenu');
if (profileBtn && dropdownMenu) {
    profileBtn.addEventListener('click', () => dropdownMenu.classList.toggle('show'));
    window.addEventListener('click', e => {
        if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.remove('show');
        }
    });
}
</script>
</body>
</html>

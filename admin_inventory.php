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
    if ($_POST['action'] === 'delete_ingredient' && $ingredientId > 0) {
        $stmt = $pdo->prepare("DELETE FROM ingredientes WHERE id = :id");
        $stmt->execute([':id' => $ingredientId]);
        $message = 'Ingrediente eliminado correctamente.';
    }

    if ($ingredientId > 0 && hasTableColumn($pdo, 'ingredientes', 'cantidad')) {
        if ($_POST['action'] === 'update_stock') {
            $change = floatval($_POST['change'] ?? 0);
            $stmt = $pdo->prepare("UPDATE ingredientes SET cantidad = GREATEST(0, cantidad + :delta) WHERE id = :id");
            $stmt->execute([':delta' => $change, ':id' => $ingredientId]);
            $message = 'Stock actualizado.';
            notifyCriticalIngredient($pdo, $ingredientId, (int)$_SESSION['user_id']);
        }
        if ($_POST['action'] === 'edit_threshold' && hasTableColumn($pdo, 'ingredientes', 'stock_minimo')) {
            $min = max(0, floatval($_POST['min_quantity'] ?? 0));
            $stmt = $pdo->prepare("UPDATE ingredientes SET stock_minimo = :min WHERE id = :id");
            $stmt->execute([':min' => $min, ':id' => $ingredientId]);
            $message = 'Umbral actualizado.';
            notifyCriticalIngredient($pdo, $ingredientId, (int)$_SESSION['user_id']);
        }
    }
    if ($_POST['action'] === 'add_ingredient') {
        $name = trim($_POST['name'] ?? '');
        $initialStock = max(0, floatval($_POST['initial_stock'] ?? 0));
        $threshold = max(0, floatval($_POST['threshold'] ?? 0));
        $unit = trim($_POST['unit'] ?? 'unidades');
        $status = trim($_POST['status'] ?? 'Activo');

        if ($name === '') {
            $message = 'El nombre del ingrediente es obligatorio.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO ingredientes (nombre, cantidad, stock_minimo, unidad) VALUES (:name, :cantidad, :stock_minimo, :unidad)");
            $stmt->execute([
                ':name' => $name,
                ':cantidad' => $initialStock,
                ':stock_minimo' => $threshold,
                ':unidad' => $unit ?: 'unidades'
            ]);
            $message = 'Ingrediente añadido correctamente.';
            $stmt = $pdo->query(
                "SELECT id, nombre, cantidad, unidad, stock_minimo, COALESCE(unidad, 'unidades') AS safe_unit, COALESCE(stock_minimo, 1) AS safe_min
                 FROM ingredientes
                 ORDER BY nombre ASC"
            );
            $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

try {
    $stmt = $pdo->query(
        "SELECT id, nombre, cantidad, unidad, stock_minimo, COALESCE(unidad, 'unidades') AS safe_unit, COALESCE(stock_minimo, 1) AS safe_min
         FROM ingredientes
         ORDER BY nombre ASC"
    );
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $ingredients = [];
    $message = 'Error al cargar ingredientes: ' . htmlspecialchars($e->getMessage());
}

notifyCriticalIngredients($pdo, (int)$_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Inventario</title>
<link rel="icon" type="image/png" href="assets/favicon.png">
<link rel="shortcut icon" type="image/png" href="assets/favicon.png">
<link rel="shortcut icon" type="image/png" href="assets/fabiconig.png">
<link rel="stylesheet" href="styles.css?v=20260513-1">
<style>
.stock-badge { display:inline-block; min-width:72px; padding:.35rem .7rem; border-radius:999px; color:#fff; font-size:.85rem; }
.stock-verde { background:#2ecc71; }
.stock-amarillo { background:#f1c40f; color:#222; }
.stock-rojo { background:#e74c3c; }
.stock-gris { background:#95a5a6; }
.table-scroll { overflow-x:auto; width:100%; }
.inventory-table { width:100%; border-collapse: collapse; min-width:700px; }
.inventory-table th, .inventory-table td { padding:.7rem .6rem; border:1px solid #e7e7e7; text-align:left; white-space:nowrap; }
.inventory-table th { background:#f9f9f9; }
.inv-actions { display:flex; gap:6px; flex-wrap:wrap; }
.inv-actions .inv-row { display:flex; gap:6px; }
.inv-actions form { display:flex; align-items:center; gap:4px; }
.inv-actions button { padding:4px 8px; font-size:.8rem; white-space:nowrap; }
.inv-actions input[type=number] { width:55px; padding:3px 5px; font-size:.8rem; border:1px solid #ccc; border-radius:4px; }
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
      <button class="profile-btn" onclick="toggleProfile()">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="white">
          <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c1.52 0 5.1 1.34 5.1 5v1H6.9v-1c0-3.66 3.58-5 5.1-5z"/>
        </svg>
      </button>
      <span class="user-name"><?= htmlspecialchars($display_name) ?></span>
      <div class="dropdown" id="profileDropdown">
          <a href="perfil.php" data-i18n="nav.myProfile">Mi perfil</a>
          <a href="politica_cookies.php" class="open-cookie-preferences" data-i18n="nav.customizeCookies">Personalizar cookies</a>
          <a href="logout.php" data-i18n="nav.logout">Cerrar sesión</a>
      </div>
    </div>
    <a href="admin.php" class="landing-logo"><span class="landing-logo-text">Zyma</span></a>
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
                <a href="admin.php" class="landing-link">Volver a inicio</a>
            </div>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if (empty($ingredients)): ?>
            <p class="empty-state">No hay ingredientes registrados.</p>
        <?php else: ?>
            <div class="table-scroll">
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
                            <td class="inv-actions">
                                <div class="inv-row">
                                    <form method="POST" class="inv-form">
                                        <input type="hidden" name="action" value="edit_threshold">
                                        <input type="hidden" name="ingredient_id" value="<?= (int)$ingredient['id'] ?>">
                                        <input type="number" step="0.1" name="min_quantity" value="<?= htmlspecialchars($minQuantity) ?>">
                                        <button type="submit" class="btn-add-cart">Umbral</button>
                                    </form>
                                    <form method="POST" class="inv-form">
                                        <input type="hidden" name="action" value="update_stock">
                                        <input type="hidden" name="ingredient_id" value="<?= (int)$ingredient['id'] ?>">
                                        <input type="number" step="0.1" name="change" placeholder="+/-">
                                        <button type="submit" class="btn-add-cart">Stock</button>
                                    </form>
                                </div>
                                <form method="POST" onsubmit="return confirm('¿Eliminar este ingrediente?');">
                                    <input type="hidden" name="action" value="delete_ingredient">
                                    <input type="hidden" name="ingredient_id" value="<?= (int)$ingredient['id'] ?>">
                                    <button type="submit" class="remove-item-btn">Borrar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>

        <div style="margin-top:1.5rem;">
            <h3>Añadir ingrediente</h3>
            <form method="POST" style="display:grid; gap:1rem; max-width:500px;">
                <input type="hidden" name="action" value="add_ingredient">
                <input type="text" name="name" placeholder="Nombre del ingrediente" required>
                <input type="number" step="0.1" name="initial_stock" placeholder="Stock inicial" min="0" required>
                <input type="number" step="0.1" name="threshold" placeholder="Umbral mínimo" min="0" required>
                <input type="text" name="unit" placeholder="Unidad (ej: kg, litros, unidades)" value="unidades">
                <select name="status">
                    <option value="Activo" selected>Activo</option>
                    <option value="Inactivo">Inactivo</option>
                </select>
                <button type="submit" class="btn-add-cart">Añadir ingrediente</button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleProfile() {
    var d = document.getElementById('profileDropdown');
    if (d) d.classList.toggle('show');
}

document.addEventListener('click', function(e) {
    var p = document.getElementById('profileDropdown');
    if (p && !e.target.closest('.profile-section')) {
        p.classList.remove('show');
    }
});
</script>
</body>
</html>

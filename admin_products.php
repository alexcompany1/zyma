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
    if ($_POST['action'] === 'store') {
        $name = trim($_POST['name'] ?? '');
        $price = max(0, floatval($_POST['price'] ?? 0));
        $category = trim($_POST['category'] ?? 'General');
        $description = trim($_POST['description'] ?? '');
        $available = isset($_POST['available']) ? 1 : 0;

        if ($name === '') {
            $message = 'El nombre del producto es obligatorio.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO productos (nombre, precio, category, description, available) VALUES (:name, :price, :category, :description, :available)");
            $stmt->execute([
                ':name' => $name,
                ':price' => $price,
                ':category' => $category ?: 'General',
                ':description' => $description,
                ':available' => $available
            ]);
            $message = 'Producto añadido correctamente.';
        }
    }
    if ($_POST['action'] === 'update') {
        $id = intval($_POST['product_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $price = max(0, floatval($_POST['price'] ?? 0));
        $category = trim($_POST['category'] ?? 'General');
        $available = isset($_POST['available']) ? 1 : 0;

        if ($id > 0 && $name !== '') {
            $stmt = $pdo->prepare("UPDATE productos SET nombre = :name, precio = :price, category = :category, available = :available WHERE id = :id");
            $stmt->execute([
                ':name' => $name,
                ':price' => $price,
                ':category' => $category,
                ':available' => $available,
                ':id' => $id
            ]);
            $message = 'Producto actualizado correctamente.';
        }
    }
    if ($_POST['action'] === 'destroy') {
        $id = intval($_POST['product_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE productos SET deleted_at = NOW() WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $message = 'Producto eliminado del catálogo.';
        }
    }
    if ($_POST['action'] === 'toggle_available') {
        $id = intval($_POST['product_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE productos SET available = CASE WHEN available = 1 THEN 0 ELSE 1 END WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $message = 'Disponibilidad actualizada.';
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}

$filterCategory = trim($_GET['filter_category'] ?? '');
$searchName = trim($_GET['search'] ?? '');

$where = ['deleted_at IS NULL'];
$params = [];
if ($filterCategory !== '') {
    $where[] = 'category = :category';
    $params[':category'] = $filterCategory;
}
if ($searchName !== '') {
    $where[] = 'nombre LIKE :search';
    $params[':search'] = '%' . $searchName . '%';
}
$whereSql = implode(' AND ', $where);

try {
    $stmt = $pdo->prepare("SELECT id, nombre, precio, category, description, available FROM productos WHERE {$whereSql} ORDER BY category, nombre");
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $categories = array_unique(array_map(fn($p) => $p['category'] ?: 'General', $products));
    sort($categories, SORT_STRING);
} catch (Exception $e) {
    $products = [];
    $categories = [];
    $message = 'Error al cargar productos: ' . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Productos</title>
<link rel="icon" type="image/png" href="assets/fabiconig.png">
<link rel="shortcut icon" type="image/png" href="assets/fabiconig.png">
<link rel="stylesheet" href="styles.css?v=20260211-5">
<style>
.table-compact { width:100%; border-collapse:collapse; }
.table-compact th, .table-compact td { padding:.85rem .75rem; border:1px solid #e7e7e7; }
.table-compact th { background:#f9f9f9; }
.product-actions { display:flex; gap:.5rem; flex-wrap:wrap; }

#editOverlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 99999;
    display: none;
    justify-content: center;
    align-items: center;
}

#editBox {
    background: white;
    padding: 25px;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}

#editBox h3 { margin-top:0; margin-bottom:15px; }
#editBox form { display:grid; gap:12px; }
#editBox input { width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; box-sizing:border-box; }
#editBox label { display:flex; align-items:center; gap:8px; }
#editBoxButtons { display:flex; justify-content:flex-end; gap:10px; margin-top:15px; }

.landing-bar.mobile-ready .profile-section { display: flex !important; }
.landing-bar.mobile-ready .quick-menu-section { display: flex !important; }
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
          <a href="perfil.php" data-i18n="nav.myProfile">Mi perfil</a>
          <a href="politica_cookies.php" class="open-cookie-preferences" data-i18n="nav.customizeCookies">Personalizar cookies</a>
          <a href="logout.php" data-i18n="nav.logout">Cerrar sesión</a>
      </div>
    </div>
    <a href="admin.php" class="landing-logo"><span class="landing-logo-text">Zyma</span></a>
        <div class="quick-menu-section">
      <button class="quick-menu-btn" id="quickMenuBtn" aria-label="Menú rápido">
        <svg class="quick-menu-icon" viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
          <path d="M5 7h14M5 12h14M5 17h14" />
        </svg>
      </button>
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
                <h2>Catálogo de productos</h2>
                <p class="lead">Crea, edita y administra el catálogo con disponibilidad y soft delete.</p>
            </div>
            <div>
                <a href="usuario.php" class="landing-link">Volver a inicio</a>
            </div>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="GET" class="row-between" style="gap:1rem; flex-wrap:wrap; margin-bottom:1.2rem;">
            <select name="filter_category">
                <option value="">Todas las categorías</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category) ?>" <?= $category === $filterCategory ? 'selected' : '' ?>><?= htmlspecialchars($category) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="search" name="search" placeholder="Buscar por nombre" value="<?= htmlspecialchars($searchName) ?>">
            <button type="submit" class="btn-add-cart">Filtrar</button>
        </form>

        <table class="table-compact">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Disponibilidad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="5" class="empty-state">No hay productos en el catálogo.</td></tr>
                <?php endif; ?>
                <?php foreach ($products as $product): ?>
                <?php
                $pid = (int)$product['id'];
                $pname = htmlspecialchars($product['nombre']);
                $pprice = (float)$product['precio'];
                $pcat = htmlspecialchars($product['category'] ?: 'General');
                $pavail = (int)$product['available'];
                ?>
                    <tr>
                        <td><?= $pname ?></td>
                        <td><?= $pcat ?></td>
                        <td>€<?= number_format($pprice, 2, ',', '.') ?></td>
                        <td><button type="button" class="toggle-availability btn-add-cart" data-id="<?= $pid ?>"><?= Product::availableLabel($pavail) ?></button></td>
                        <td class="product-actions">
                            <button type="button" class="btn-add-cart" onclick="abrirEditar(<?= $pid ?>,'<?= addslashes($product['nombre']) ?>',<?= $pprice ?>,'<?= addslashes($product['category'] ?: 'General') ?>',<?= $pavail ?>)">Editar</button>
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="action" value="destroy">
                                <input type="hidden" name="product_id" value="<?= $pid ?>">
                                <button type="submit" class="remove-item-btn" onclick="return confirm('Eliminar producto?');">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top:1.5rem;">
            <h3>Añadir producto</h3>
            <form method="POST" style="display:grid; gap:1rem; max-width:500px;">
                <input type="hidden" name="action" value="store">
                <input type="text" name="name" placeholder="Nombre" required>
                <input type="number" name="price" step="0.01" placeholder="Precio" required>
                <input type="text" name="category" placeholder="Categoría" value="General">
                <textarea name="description" placeholder="Descripción" rows="3"></textarea>
                <label><input type="checkbox" name="available" checked> Disponible</label>
                <button type="submit" class="btn-add-cart">Crear producto</button>
            </form>
        </div>
    </div>
</div>

<div id="editOverlay">
    <div id="editBox">
        <h3>Editar producto</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="product_id" id="edit_id">
            <label>Nombre:</label>
            <input type="text" name="name" id="edit_nombre" required>
            <label>Precio:</label>
            <input type="number" name="price" id="edit_precio" step="0.01" required>
            <label>Categoría:</label>
            <input type="text" name="category" id="edit_categoria">
            <label><input type="checkbox" name="available" id="edit_disponible"> Disponible</label>
            <div id="editBoxButtons">
                <button type="button" class="remove-item-btn" onclick="cerrarEditar()">Cancelar</button>
                <button type="submit" class="btn-add-cart">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirEditar(id, nombre, precio, categoria, disponible) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_precio').value = precio;
    document.getElementById('edit_categoria').value = categoria;
    document.getElementById('edit_disponible').checked = (disponible === 1);
    document.getElementById('editOverlay').style.display = 'flex';
}

function cerrarEditar() {
    document.getElementById('editOverlay').style.display = 'none';
}

document.getElementById('editOverlay').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarEditar();
    }
});

document.querySelectorAll('.toggle-availability').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        var self = this;
        var formData = new FormData();
        formData.append('action', 'toggle_available');
        formData.append('product_id', id);

        fetch('admin_products.php', {
            method: 'POST',
            body: formData
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) {
                var txt = self.textContent.trim();
                self.textContent = txt === 'Disponible' ? 'No disponible' : 'Disponible';
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    var profileBtn = document.getElementById('profileBtn');
    var dropdownMenu = document.getElementById('dropdownMenu');
    var quickBtn = document.getElementById('quickMenuBtn');
    var quickDropdown = document.getElementById('quickDropdown');

    if (profileBtn && dropdownMenu) {
        profileBtn.addEventListener('click', function() {
            dropdownMenu.classList.toggle('show');
        });
    }

    if (quickBtn && quickDropdown) {
        quickBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            quickDropdown.classList.toggle('show');
        });
    }

    document.addEventListener('click', function(e) {
        if (profileBtn && dropdownMenu && !profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.remove('show');
        }
        if (quickBtn && quickDropdown && !quickBtn.contains(e.target) && !quickDropdown.contains(e.target)) {
            quickDropdown.classList.remove('show');
        }
    });
});
</script>
<script src="assets/mobile-header.js?v=20260211-6"></script>
</body>
</html>

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
        $description = trim($_POST['description'] ?? '');
        $available = isset($_POST['available']) ? 1 : 0;

        if ($id > 0 && $name !== '') {
            $stmt = $pdo->prepare("UPDATE productos SET nombre = :name, precio = :price, category = :category, description = :description, available = :available WHERE id = :id");
            $stmt->execute([
                ':name' => $name,
                ':price' => $price,
                ':category' => $category,
                ':description' => $description,
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
<link rel="icon" type="image/png" href="assets/favicon.png">
<link rel="stylesheet" href="styles.css?v=20260211-5">
<style>
.table-compact { width:100%; border-collapse:collapse; }
.table-compact th, .table-compact td { padding:.85rem .75rem; border:1px solid #e7e7e7; }
.table-compact th { background:#f9f9f9; }
.product-actions { display:flex; gap:.5rem; flex-wrap:wrap; }
.modal-backdrop { position: fixed; inset:0; background:rgba(0,0,0,.45); display:none; align-items:center; justify-content:center; z-index:50; }
.modal { background:#fff; border-radius:12px; width:min(640px,95vw); padding:1.2rem; box-shadow:0 22px 60px rgba(0,0,0,.18); }
.modal.show { display:flex; }
.modal h3 { margin-top:0; }
.modal form { display:grid; gap:1rem; }
.modal input, .modal textarea, .modal select { width:100%; padding:.8rem; border:1px solid #ccc; border-radius:8px; }
.modal-footer { display:flex; justify-content:flex-end; gap:.75rem; margin-top:1rem; }
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
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="6" class="empty-state">No hay productos en el catálogo.</td></tr>
                <?php endif; ?>
                <?php foreach ($products as $product): ?>
                    <tr data-id="<?= (int)$product['id'] ?>">
                        <td><?= htmlspecialchars($product['nombre']) ?></td>
                        <td><?= htmlspecialchars($product['category'] ?: 'General') ?></td>
                        <td>€<?= number_format((float)$product['precio'], 2, ',', '.') ?></td>
                        <td>
                            <button type="button" class="toggle-availability btn-add-cart" data-id="<?= (int)$product['id'] ?>">
                                <?= Product::availableLabel((int)$product['available']) ?>
                            </button>
                        </td>
                        <td><?= htmlspecialchars(mb_strimwidth($product['description'] ?? '', 0, 80, '...')) ?></td>
                        <td class="product-actions">
                            <button type="button" class="btn-add-cart edit-product" data-product='<?= json_encode($product, JSON_HEX_APOS|JSON_HEX_QUOT) ?>'>Editar</button>
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="action" value="destroy">
                                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
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

<div class="modal-backdrop" id="modalBackdrop">
    <div class="modal" role="dialog" aria-modal="true">
        <h3>Editar producto</h3>
        <form id="editProductForm" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="product_id" id="editProductId">
            <input type="text" name="name" id="editName" placeholder="Nombre" required>
            <input type="number" name="price" id="editPrice" step="0.01" placeholder="Precio" required>
            <input type="text" name="category" id="editCategory" placeholder="Categoría">
            <textarea name="description" id="editDescription" rows="4" placeholder="Descripción"></textarea>
            <label><input type="checkbox" name="available" id="editAvailable"> Disponible</label>
            <div class="modal-footer">
                <button type="button" class="remove-item-btn" id="closeModal">Cancelar</button>
                <button type="submit" class="btn-add-cart">Guardar cambios</button>
            </div>
        </form>
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

const modalBackdrop = document.getElementById('modalBackdrop');
const editForm = document.getElementById('editProductForm');
const editProductId = document.getElementById('editProductId');
const editName = document.getElementById('editName');
const editPrice = document.getElementById('editPrice');
const editCategory = document.getElementById('editCategory');
const editDescription = document.getElementById('editDescription');
const editAvailable = document.getElementById('editAvailable');
const closeModal = document.getElementById('closeModal');

function openModal(product) {
    editProductId.value = product.id;
    editName.value = product.nombre;
    editPrice.value = product.precio;
    editCategory.value = product.category || 'General';
    editDescription.value = product.description || '';
    editAvailable.checked = Number(product.available) === 1;
    modalBackdrop.classList.add('show');
}

function closeProductModal() {
    modalBackdrop.classList.remove('show');
}

document.querySelectorAll('.edit-product').forEach(button => {
    button.addEventListener('click', () => {
        const product = JSON.parse(button.getAttribute('data-product'));
        openModal(product);
    });
});

closeModal.addEventListener('click', closeProductModal);
modalBackdrop.addEventListener('click', e => {
    if (e.target === modalBackdrop) {
        closeProductModal();
    }
});

const toggles = document.querySelectorAll('.toggle-availability');
toggles.forEach(button => {
    button.addEventListener('click', () => {
        const id = button.dataset.id;
        const formData = new FormData();
        formData.append('action', 'toggle_available');
        formData.append('product_id', id);

        fetch('admin_products.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json()).then(data => {
            if (data.success) {
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    const nameCell = row.cells[0];
                    const availableButton = row.querySelector('.toggle-availability');
                    if (availableButton) {
                        const current = availableButton.textContent.trim();
                        availableButton.textContent = current === 'Disponible' ? 'No disponible' : 'Disponible';
                    }
                }
            }
        }).catch(console.error);
    });
});
</script>
</body>
</html>

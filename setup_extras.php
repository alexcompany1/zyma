<?php
session_start();
require_once 'config.php';
require_once 'auth.php';
zymaRequireRole('admin');

$log = [];
$errors = [];

function runStep(PDO $pdo, string $label, string $sql, array &$log, array &$errors): void {
    try {
        $pdo->exec($sql);
        $log[] = "OK: $label";
    } catch (Throwable $e) {
        $errors[] = "ERROR en \"$label\": " . $e->getMessage();
    }
}

// 1. Crear tablas si no existen
runStep($pdo, 'Crear product_extras', "
    CREATE TABLE IF NOT EXISTS product_extras (
        id INT(11) NOT NULL AUTO_INCREMENT,
        nombre VARCHAR(100) NOT NULL,
        precio_adicional DECIMAL(6,2) NOT NULL DEFAULT 0.00,
        activo TINYINT(1) DEFAULT 1,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", $log, $errors);

runStep($pdo, 'Crear product_allergens', "
    CREATE TABLE IF NOT EXISTS product_allergens (
        id INT(11) NOT NULL AUTO_INCREMENT,
        nombre VARCHAR(100) NOT NULL,
        icono VARCHAR(10) DEFAULT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", $log, $errors);

// 1b. Añadir columna extras a pedido_items si no existe
runStep($pdo, 'Anadir columna extras a pedido_items', "
    ALTER TABLE pedido_items ADD COLUMN extras TEXT NULL AFTER precio_unitario
", $log, $errors);

runStep($pdo, 'Crear producto_extras', "
    CREATE TABLE IF NOT EXISTS producto_extras (
        id INT(11) NOT NULL AUTO_INCREMENT,
        id_producto INT(11) NOT NULL,
        id_extra INT(11) NOT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE,
        FOREIGN KEY (id_extra) REFERENCES product_extras(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", $log, $errors);

runStep($pdo, 'Crear producto_allergens', "
    CREATE TABLE IF NOT EXISTS producto_allergens (
        id INT(11) NOT NULL AUTO_INCREMENT,
        id_producto INT(11) NOT NULL,
        id_allergen INT(11) NOT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE,
        FOREIGN KEY (id_allergen) REFERENCES product_allergens(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", $log, $errors);

// 2. Insertar extras base (si no existen por nombre)
$extrasBase = [
    ['Queso extra',         1.00],
    ['Bacon',               1.50],
    ['Cebolla caramelizada',0.80],
    ['Jalapeños',           0.50],
    ['Salsa BBQ extra',     0.60],
    ['Barbacoa',            0.30],
    ['Ketchup',             0.00],
    ['Alioli',              0.30],
    ['Mayonesa',            0.00],
    ['Mostaza',             0.00],
    ['Ranchera',            0.30],
];

$insertExtra = $pdo->prepare("
    INSERT INTO product_extras (nombre, precio_adicional, activo)
    SELECT ?, ?, 1 FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM product_extras WHERE nombre = ?)
");
foreach ($extrasBase as [$nombre, $precio]) {
    try {
        $insertExtra->execute([$nombre, $precio, $nombre]);
        $log[] = "Extra: \"$nombre\" listo";
    } catch (Throwable $e) {
        $errors[] = "ERROR insertando extra \"$nombre\": " . $e->getMessage();
    }
}

// 3. Recuperar IDs de extras por nombre
$extraIds = [];
try {
    $stmt = $pdo->query("SELECT id, nombre FROM product_extras");
    foreach ($stmt->fetchAll() as $row) {
        $extraIds[$row['nombre']] = (int)$row['id'];
    }
    $log[] = "IDs de extras cargados: " . implode(', ', array_keys($extraIds));
} catch (Throwable $e) {
    $errors[] = "ERROR cargando IDs de extras: " . $e->getMessage();
}

// 4. Asignar extras a productos (idempotente)
$asignaciones = [
    // [id_producto, nombre_extra]
    // Nachos con Queso (1)
    [1, 'Queso extra'], [1, 'Jalapeños'], [1, 'Salsa BBQ extra'],
    // Patatas Fritas (2) — todas las salsas
    [2, 'Barbacoa'], [2, 'Ketchup'], [2, 'Alioli'],
    [2, 'Mayonesa'], [2, 'Mostaza'], [2, 'Ranchera'],
    // Hotdog BBQ (3)
    [3, 'Queso extra'], [3, 'Bacon'], [3, 'Salsa BBQ extra'],
    [3, 'Jalapeños'], [3, 'Cebolla caramelizada'], [3, 'Barbacoa'],
    // Hotdog Clásico (4)
    [4, 'Queso extra'], [4, 'Bacon'], [4, 'Cebolla caramelizada'],
    [4, 'Jalapeños'], [4, 'Mostaza'], [4, 'Ketchup'], [4, 'Mayonesa'],
    // Hotdog Vegano (5)
    [5, 'Jalapeños'], [5, 'Cebolla caramelizada'],
    [5, 'Ketchup'], [5, 'Mostaza'], [5, 'Alioli'],
];

$insertRel = $pdo->prepare("
    INSERT INTO producto_extras (id_producto, id_extra)
    SELECT ?, ? FROM DUAL
    WHERE NOT EXISTS (
        SELECT 1 FROM producto_extras WHERE id_producto = ? AND id_extra = ?
    )
");
foreach ($asignaciones as [$idProducto, $nombreExtra]) {
    if (!isset($extraIds[$nombreExtra])) continue;
    $idExtra = $extraIds[$nombreExtra];
    try {
        $insertRel->execute([$idProducto, $idExtra, $idProducto, $idExtra]);
    } catch (Throwable $e) {
        $errors[] = "ERROR asignando \"$nombreExtra\" a producto $idProducto: " . $e->getMessage();
    }
}
$log[] = "Asignaciones producto-extra completadas";

$success = empty($errors);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Setup Extras - Zyma</title>
<link rel="stylesheet" href="styles.css?v=20260428-1">
<style>
body{padding:30px}
.setup-box{max-width:700px;margin:0 auto;background:#fff;border-radius:16px;padding:30px;box-shadow:0 4px 20px rgba(0,0,0,.1)}
h1{color:#45050C;margin-bottom:20px}
.log-ok{color:#2d7a2d;font-size:.9rem;margin:3px 0}
.log-err{color:#c0392b;font-size:.9rem;margin:3px 0;font-weight:600}
.result-ok{background:#eafaea;border:1px solid #6ec96e;border-radius:10px;padding:14px;margin-bottom:16px;color:#1a5e1a}
.result-err{background:#fdf0f0;border:1px solid #e08080;border-radius:10px;padding:14px;margin-bottom:16px;color:#8b0000}
.btn-back{display:inline-block;margin-top:20px;padding:10px 24px;background:#45050C;color:#fff;border-radius:10px;text-decoration:none}
</style>
</head>
<body>
<div class="setup-box">
  <h1>Setup de Extras</h1>

  <?php if ($success): ?>
    <div class="result-ok"><strong>Todo listo.</strong> Las tablas y extras se han configurado correctamente.</div>
  <?php else: ?>
    <div class="result-err"><strong>Hubo algunos errores:</strong>
      <?php foreach ($errors as $e): ?>
        <p class="log-err"><?= htmlspecialchars($e) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <details>
    <summary style="cursor:pointer;color:#45050C;margin-top:10px">Ver log completo</summary>
    <div style="margin-top:10px">
      <?php foreach ($log as $line): ?>
        <p class="log-ok"><?= htmlspecialchars($line) ?></p>
      <?php endforeach; ?>
    </div>
  </details>

  <a href="carta.php" class="btn-back">Ir a la carta</a>
</div>
</body>
</html>

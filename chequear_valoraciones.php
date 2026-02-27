<?php
/**
 * chequear_valoraciones.php
 * Verificación rápida del estado
 */

require_once 'config.php';

$tabla_existe = false;
$error = '';

try {
    $resultado = $pdo->query("SHOW TABLES LIKE 'valoraciones'")->fetch();
    $tabla_existe = (bool)$resultado;
} catch (Exception $e) {
    $error = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Estado de Valoraciones</title>
<link rel="stylesheet" href="styles.css?v=20260211-5">
</head>
<body>
<div class="container">
    <div class="main-content">
        <h2 class="welcome">Estado del Sistema de Valoraciones</h2>
        
        <div style="font-size: 1.5rem; margin: 2rem 0; padding: 2rem; background: <?= $tabla_existe ? '#d4edda' : '#f8d7da' ?>; border-radius: 8px; text-align: center;">
            <?php if ($tabla_existe): ?>
                <strong style="color: #28a745;">TABLA ACTIVA</strong>
                <p style="margin-top: 0.5rem; color: #155724;">El sistema de valoraciones está funcionando correctamente.</p>
            <?php else: ?>
                <strong style="color: #dc3545;">TABLA NO EXISTE</strong>
                <p style="margin-top: 0.5rem; color: #721c24;">Necesitas instalar el sistema.</p>
                <?php if ($error): ?>
                    <p style="font-size: 0.9rem; margin-top: 0.5rem;">Error: <?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <?php if (!$tabla_existe): ?>
                <p style="margin-bottom: 1rem;">
                    <strong>Solución:</strong> Como trabajador, ve a tu panel y haz clic en:
                </p>
                <p style="background: #f0f0f0; padding: 1rem; border-radius: 6px; display: inline-block; margin: 1rem 0;">
                    <strong>Sistema de Valoraciones - Instalar/Activar</strong>
                </p>
            <?php else: ?>
                <a href="valoraciones.php" class="btn-add-cart">Ir a Valoraciones</a>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>

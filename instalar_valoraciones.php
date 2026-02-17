<?php
/**
 * instalar_valoraciones.php
 * Instalación rápida del sistema de valoraciones
 */

session_start();

// Solo trabajadores
if (!isset($_SESSION['user_id']) || !$_SESSION['worker_code']) {
    die("Acceso denegado");
}

require_once 'config.php';

$mensaje = '';
$tipo_alerta = '';
$tabla_existe = false;

// Intentar crear tabla
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instalar'])) {
    try {
        $sql = "CREATE TABLE IF NOT EXISTS valoraciones (
            id INT PRIMARY KEY AUTO_INCREMENT,
            id_usuario INT NOT NULL,
            FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
            id_producto INT NOT NULL,
            FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE,
            puntuacion INT NOT NULL CHECK (puntuacion >= 1 AND puntuacion <= 5),
            comentario TEXT,
            fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_usuario_producto (id_usuario, id_producto),
            INDEX idx_usuario (id_usuario),
            INDEX idx_producto (id_producto),
            INDEX idx_puntuacion (puntuacion),
            INDEX idx_fecha (fecha_creacion)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $pdo->exec($sql);
        
        // Verificar
        $verificacion = $pdo->query("SHOW TABLES LIKE 'valoraciones'")->fetch();
        if ($verificacion) {
            $mensaje = "✓ ¡Tabla creada correctamente! Ya puedes valorar productos.";
            $tipo_alerta = "success";
            $tabla_existe = true;
        }
    } catch (Exception $e) {
        $mensaje = "✗ Error: " . $e->getMessage();
        $tipo_alerta = "error";
    }
} else {
    // Verificar si tabla existe
    try {
        $resultado = $pdo->query("SHOW TABLES LIKE 'valoraciones'")->fetch();
        $tabla_existe = (bool)$resultado;
    } catch (Exception $e) {
        $tabla_existe = false;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instalar Valoraciones</title>
<link rel="stylesheet" href="styles.css?v=20260211-5">
</head>
<body>
<div class="container">
    <div class="main-content">
        <h2 class="welcome">Instalador de Valoraciones</h2>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipo_alerta ?> alert-spaced">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <?php if ($tabla_existe): ?>
            <div class="alert alert-success alert-spaced">
                <strong>✓ Status:</strong> Tabla de valoraciones está activa y funcionando.
            </div>
            
            <div style="margin-top: 2rem;">
                <a href="valoraciones.php" class="btn-add-cart">Ir a Valoraciones</a>
                <a href="trabajador.php" class="btn-secondary">Volver al Panel</a>
            </div>
        <?php else: ?>
            <div class="alert alert-warning alert-spaced" style="margin: 1.5rem 0;">
                <strong>⚠️ Atención:</strong> La tabla de valoraciones no está instalada.
            </div>

            <p style="margin: 1.5rem 0; line-height: 1.6;">
                El sistema de valoraciones permite que los clientes puntúen y comenten sobre los productos. 
                Haz clic en el botón de abajo para instalar la tabla automáticamente.
            </p>

            <form method="POST" style="margin: 2rem 0;">
                <button type="submit" name="instalar" class="btn-add-cart" style="font-size: 1.2rem; padding: 0.8rem 2rem;">
                    🔧 Instalar Tabla de Valoraciones
                </button>
            </form>

            <div style="background: #f5f5f5; padding: 1.5rem; border-radius: 8px; margin-top: 2rem;">
                <h3>¿Qué hace esto?</h3>
                <ul style="margin: 1rem 0; padding-left: 1.5rem;">
                    <li>Crea la tabla <code>valoraciones</code> en la BD</li>
                    <li>Permite a clientes dejar reseñas con puntuación (1-5 estrellas)</li>
                    <li>Requiere estar logueado para valorar</li>
                    <li>Cada cliente solo puede valorar cada producto una vez</li>
                </ul>
            </div>
        <?php endif; ?>

        <div style="margin-top: 2rem; text-align: center;">
            <a href="diagnostico_valoraciones.php" class="btn-secondary">Ver Diagnóstico</a>
            <a href="trabajador.php" class="btn-secondary">Volver</a>
        </div>
    </div>
</div>

<footer>
    <p>&copy; 2025 Zyma. Todos los derechos reservados.</p>
</footer>

</body>
</html>

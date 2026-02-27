<?php
/**
 * init_valoraciones.php
 * Script de inicialización del sistema de valoraciones
 * Ejecuta el script SQL para crear la tabla si no existe
 */

session_start();

// Verificar si es trabajador
if (!isset($_SESSION['user_id']) || !$_SESSION['worker_code']) {
    die("Acceso denegado. Solo trabajadores pueden ejecutar esto.");
}

require_once 'config.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inicializar Valoraciones</title>
<link rel="stylesheet" href="styles.css?v=20260211-5">
</head>
<body>
<div class="container">
    <div class="main-content">
        <h2 class="welcome">Inicializar Sistema de Valoraciones</h2>
        
        <?php
            try {
                // Intentar crear la tabla directamente
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
                
                // Verificar que la tabla fue creada
                $stmt = $pdo->query("SHOW TABLES LIKE 'valoraciones'");
                $resultado = $stmt->fetch();
                
                if ($resultado) {
                    echo '<div class="alert alert-success alert-spaced">';
                    echo '<strong>Exito!</strong> La tabla de valoraciones fue creada correctamente.';
                    echo '</div>';
                    
                    // Mostrar estructura de la tabla
                    $stmt = $pdo->query("DESCRIBE valoraciones");
                    $columnas = $stmt->fetchAll();
                    
                    echo '<h3>Estructura de la tabla:</h3>';
                    echo '<table style="width: 100%; border-collapse: collapse; margin: 1rem 0;">';
                    echo '<tr style="background: #f0f0f0;"><th style="padding: 0.5rem; border: 1px solid #ccc; text-align: left;">Campo</th><th style="padding: 0.5rem; border: 1px solid #ccc; text-align: left;">Tipo</th></tr>';
                    foreach ($columnas as $col) {
                        echo '<tr>';
                        echo '<td style="padding: 0.5rem; border: 1px solid #ccc;">' . htmlspecialchars($col['Field']) . '</td>';
                        echo '<td style="padding: 0.5rem; border: 1px solid #ccc;">' . htmlspecialchars($col['Type']) . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    
                    // Verificar datos
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM valoraciones");
                    $count = $stmt->fetch();
                    
                    echo '<p style="margin-top: 1rem;"><strong>Valoraciones registradas:</strong> ' . $count['total'] . '</p>';
                } else {
                    echo '<div class="alert alert-error alert-spaced">';
                    echo '<strong>Error:</strong> La tabla no se creó correctamente.';
                    echo '</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="alert alert-error alert-spaced">';
                echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage());
                echo '</div>';
            }
        ?>
        
        <div style="margin-top: 2rem;">
            <a href="trabajador.php" class="btn-secondary">Volver al Panel</a>
            <a href="valoraciones.php" class="btn-secondary">Ir a Valoraciones</a>
        </div>
    </div>
</div>

<footer>
    <p>&copy; 2025 Zyma. Todos los derechos reservados.</p>
</footer>

</body>
</html>

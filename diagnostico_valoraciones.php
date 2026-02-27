<?php
/**
 * diagnostico_valoraciones.php
 * Script de diagnóstico para verificar problemas con el sistema de valoraciones
 */

session_start();

// Verificar si es trabajador o admin
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
<title>Diagnóstico de Valoraciones</title>
<link rel="stylesheet" href="styles.css?v=20260211-5">
<style>
    .diagnostic-section { margin: 2rem 0; padding: 1.5rem; background: #f5f5f5; border-radius: 8px; }
    .status-ok { color: #28a745; font-weight: bold; }
    .status-error { color: #dc3545; font-weight: bold; }
    .status-warning { color: #ffc107; font-weight: bold; }
    .code-block { background: #2d2d2d; color: #f8f8f2; padding: 1rem; border-radius: 4px; overflow-x: auto; font-family: monospace; font-size: 0.85rem; }
</style>
</head>
<body>
<div class="container">
    <div class="main-content">
        <h2 class="welcome">Diagnóstico del Sistema de Valoraciones</h2>
        
        <?php
            // 1. Verificar archivo database_valoraciones.sql
            echo '<div class="diagnostic-section">';
            echo '<h3>1. Archivo SQL</h3>';
            if (file_exists('database_valoraciones.sql')) {
                echo '<p><span class="status-ok">BIEN:</span> Archivo database_valoraciones.sql existe</p>';
                $size = filesize('database_valoraciones.sql');
                echo '<p>Tamaño: ' . $size . ' bytes</p>';
            } else {
                echo '<p><span class="status-error">ERROR:</span> Archivo database_valoraciones.sql NO existe</p>';
                echo '<p>Ruta esperada: ' . realpath('.') . '/database_valoraciones.sql</p>';
            }
            echo '</div>';

            // 2. Verificar tabla en base de datos
            echo '<div class="diagnostic-section">';
            echo '<h3>2. Tabla en Base de Datos</h3>';
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE 'valoraciones'");
                if ($stmt->fetch()) {
                    echo '<p><span class="status-ok">BIEN:</span> Tabla valoraciones existe</p>';
                    
                    // Verificar estructura
                    $stmt = $pdo->query("DESCRIBE valoraciones");
                    $columns = $stmt->fetchAll();
                    
                    echo '<p>Campos en la tabla:</p>';
                    echo '<ul>';
                    foreach ($columns as $col) {
                        echo '<li>' . htmlspecialchars($col['Field']) . ' (' . htmlspecialchars($col['Type']) . ')</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p><span class="status-warning">ADVERTENCIA:</span> Tabla valoraciones NO existe</p>';
                    echo '<p>Se intentará crear automáticamente cuando se guarde una valoración.</p>';
                }
            } catch (Exception $e) {
                echo '<p><span class="status-error">ERROR:</span> ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            echo '</div>';

            // 3. Verificar tabla productos
            echo '<div class="diagnostic-section">';
            echo '<h3>3. Tabla Productos</h3>';
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
                $result = $stmt->fetch();
                $count = $result['total'];
                echo '<p><span class="status-ok">BIEN:</span> Tabla productos existe</p>';
                echo '<p>Total de productos: ' . $count . '</p>';
                
                if ($count > 0) {
                    echo '<p>Primeros 5 productos:</p>';
                    $stmt = $pdo->query("SELECT id, nombre FROM productos LIMIT 5");
                    echo '<ul>';
                    foreach ($stmt->fetchAll() as $prod) {
                        echo '<li>ID: ' . $prod['id'] . ' - ' . htmlspecialchars($prod['nombre']) . '</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p><span class="status-warning">ADVERTENCIA:</span> No hay productos en la base de datos</p>';
                }
            } catch (Exception $e) {
                echo '<p><span class="status-error">ERROR:</span> ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            echo '</div>';

            // 4. Verificar tabla usuarios
            echo '<div class="diagnostic-section">';
            echo '<h3>4. Tabla Usuarios</h3>';
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
                $result = $stmt->fetch();
                $count = $result['total'];
                echo '<p><span class="status-ok">BIEN:</span> Tabla usuarios existe</p>';
                echo '<p>Total de usuarios: ' . $count . '</p>';
            } catch (Exception $e) {
                echo '<p><span class="status-error">ERROR:</span> ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            echo '</div>';

            // 5. Verificar valoraciones existentes
            echo '<div class="diagnostic-section">';
            echo '<h3>5. Valoraciones Existentes</h3>';
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM valoraciones");
                $result = $stmt->fetch();
                $count = $result['total'];
                echo '<p><span class="status-ok">BIEN:</span> Total de valoraciones: ' . $count . '</p>';
                
                if ($count > 0) {
                    echo '<p>Últimas 3 valoraciones:</p>';
                    $stmt = $pdo->query("
                        SELECT v.id, v.puntuacion, u.nombre, p.nombre as producto
                        FROM valoraciones v
                        JOIN usuarios u ON v.id_usuario = u.id
                        JOIN productos p ON v.id_producto = p.id
                        ORDER BY v.fecha_creacion DESC
                        LIMIT 3
                    ");
                    echo '<ul>';
                    foreach ($stmt->fetchAll() as $val) {
                        echo '<li>' . htmlspecialchars($val['nombre']) . ' - ' . htmlspecialchars($val['producto']) . ' (' . $val['puntuacion'] . ' estrellas)</li>';
                    }
                    echo '</ul>';
                }
            } catch (Exception $e) {
                echo '<p><span class="status-warning">ADVERTENCIA:</span> ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            echo '</div>';

            // 6. Verificar archivos necesarios
            echo '<div class="diagnostic-section">';
            echo '<h3>6. Archivos Necesarios</h3>';
            $archivos = [
                'valoraciones.php' => 'Página de valoraciones',
                'guardar_valoracion.php' => 'Script para guardar valoraciones',
                'assets/estrellaNegra.png' => 'Imagen estrella vacía',
                'assets/estrellaSelecionada.png' => 'Imagen estrella llena',
                'config.php' => 'Configuración de BD'
            ];
            
            foreach ($archivos as $archivo => $desc) {
                if (file_exists($archivo)) {
                    echo '<p><span class="status-ok">[OK]</span> ' . $desc . ' (' . $archivo . ')</p>';
                } else {
                    echo '<p><span class="status-error">FALTA:</span> ' . $desc . ' (' . $archivo . ')</p>';
                }
            }
            echo '</div>';

            // 7. Permisos y configuración
            echo '<div class="diagnostic-section">';
            echo '<h3>7. Configuración PHP</h3>';
            echo '<p>PHP Version: ' . phpversion() . '</p>';
            echo '<p>Extensión PDO: ' . (extension_loaded('pdo') ? '<span class="status-ok">Habilitada</span>' : '<span class="status-error">Deshabilitada</span>') . '</p>';
            echo '<p>Extensión mysqli: ' . (extension_loaded('mysqli') ? '<span class="status-ok">Habilitada</span>' : '<span class="status-error">Deshabilitada</span>') . '</p>';
            echo '</div>';
        ?>

        <div class="diagnostic-section">
            <h3>Solución Rápida</h3>
            <p>Si hay errores, haz clic en <strong>"Inicializar Valoraciones"</strong> en el panel del trabajador para recrear la tabla automáticamente.</p>
            <div style="margin-top: 1rem;">
                <a href="init_valoraciones.php" class="btn-add-cart">Inicializar Valoraciones</a>
                <a href="trabajador.php" class="btn-secondary">Volver al Panel</a>
            </div>
        </div>
    </div>
</div>

<footer>
    <p>&copy; 2025 Zyma. Todos los derechos reservados.</p>
</footer>

</body>
</html>

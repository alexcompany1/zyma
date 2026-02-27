<?php
/**
 * crear_tabla_valoraciones.php
 * Script para crear la tabla de valoraciones de forma simple y directa
 */

session_start();

// Solo trabajadores
if (!isset($_SESSION['user_id']) || !$_SESSION['worker_code']) {
    http_response_code(403);
    die("Acceso denegado");
}

require_once 'config.php';

try {
    // Verificar si tabla ya existe
    $resultado = $pdo->query("SHOW TABLES LIKE 'valoraciones'")->fetch();
    
    if ($resultado) {
        echo json_encode([
            'success' => true,
            'mensaje' => 'La tabla valoraciones ya existe',
            'creada' => false
        ]);
        exit;
    }

    // Crear tabla directamente
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
    
    // Verificar que se creó
    $verificacion = $pdo->query("SHOW TABLES LIKE 'valoraciones'")->fetch();
    
    if ($verificacion) {
        echo json_encode([
            'success' => true,
            'mensaje' => 'Tabla valoraciones creada correctamente',
            'creada' => true
        ]);
    } else {
        throw new Exception("La tabla no se creó aunque no hubo errores");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

<?php
if (!headers_sent()) {
    header('Content-Type: application/json; charset=UTF-8');
}

/**
 * guardar_valoracion.php
 * Procesa la inserción o actualización de valoraciones de PRODUCTOS.
 * Retorna un JSON con el resultado de la operación.
 */

session_start();

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Debes iniciar sesión para valorar.']);
    exit;
}

// Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
    exit;
}

// Incluir configuración de base de datos
require_once 'config.php';

// Verificar y crear tabla de valoraciones si no existe
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'valoraciones'");
    if (!$stmt->fetch()) {
        // Tabla no existe, crearla directamente
        $crearTabla = "CREATE TABLE IF NOT EXISTS valoraciones (
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
        
        $pdo->exec($crearTabla);
        error_log("Tabla de valoraciones creada automáticamente");
    }
} catch (Exception $e) {
    error_log("Error verificando tabla de valoraciones: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al abrir conexión de base de datos: ' . $e->getMessage()]);
    exit;
}

try {
    // Obtener datos (del POST)
    $id_producto = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : null;
    $puntuacion = isset($_POST['puntuacion']) ? (int)$_POST['puntuacion'] : null;
    $comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';

    // Validar producto
    if (!$id_producto || $id_producto <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Producto inválido.']);
        exit;
    }

    // Validar que el producto existe
    $stmt_prod = $pdo->prepare("SELECT id FROM productos WHERE id = :id");
    $stmt_prod->execute([':id' => $id_producto]);
    if (!$stmt_prod->fetch()) {
        http_response_code(400);
        echo json_encode(['error' => 'El producto no existe.']);
        exit;
    }

    // Validar puntuación
    if ($puntuacion === null || $puntuacion < 1 || $puntuacion > 5) {
        http_response_code(400);
        echo json_encode(['error' => 'La puntuación debe estar entre 1 y 5.']);
        exit;
    }

    // Validar comentario (opcional, máximo 300 caracteres)
    if (strlen($comentario) > 300) {
        http_response_code(400);
        echo json_encode(['error' => 'El comentario no puede superar 300 caracteres.']);
        exit;
    }

    // Obtener ID del usuario desde sesión
    $id_usuario = $_SESSION['user_id'];

    // Verificar si el usuario ya valoró este producto
    $stmt_check = $pdo->prepare("
        SELECT id FROM valoraciones 
        WHERE id_usuario = :id_usuario AND id_producto = :id_producto
    ");
    $stmt_check->execute([
        ':id_usuario' => $id_usuario,
        ':id_producto' => $id_producto
    ]);
    $valoracion_existente = $stmt_check->fetch();

    if ($valoracion_existente) {
        // Actualizar valoración existente
        $stmt = $pdo->prepare("
            UPDATE valoraciones 
            SET puntuacion = :puntuacion, 
                comentario = :comentario,
                fecha_actualizacion = CURRENT_TIMESTAMP
            WHERE id_usuario = :id_usuario AND id_producto = :id_producto
        ");
        $stmt->execute([
            ':puntuacion' => $puntuacion,
            ':comentario' => $comentario,
            ':id_usuario' => $id_usuario,
            ':id_producto' => $id_producto
        ]);
        
        $mensaje = 'Valoración actualizada correctamente.';
    } else {
        // Insertar nueva valoración
        $stmt = $pdo->prepare("
            INSERT INTO valoraciones (id_usuario, id_producto, puntuacion, comentario)
            VALUES (:id_usuario, :id_producto, :puntuacion, :comentario)
        ");
        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':id_producto' => $id_producto,
            ':puntuacion' => $puntuacion,
            ':comentario' => $comentario
        ]);
        
        $mensaje = 'Valoración guardada correctamente.';
    }

    // Retornar respuesta exitosa
    http_response_code(200);
    echo json_encode(['success' => true, 'mensaje' => $mensaje]);
    exit;

} catch (Exception $e) {
    // Error de base de datos
    $errorMsg = $e->getMessage();
    error_log("Error en guardar_valoracion.php: " . $errorMsg);
    error_log("Trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    
    // Mostrar más detalles en desarrollo (comentar en producción)
    echo json_encode([
        'error' => 'Error al procesar la valoración. Intenta de nuevo.',
        'debug' => $errorMsg  // Remover en producción
    ]);
    exit;
}
?>

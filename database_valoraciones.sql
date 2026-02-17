-- ============================================================================
-- database_valoraciones.sql
-- Script para crear la tabla de valoraciones de PRODUCTOS del restaurante.
-- Ejecutar este script en la base de datos 'zyma' antes de usar el sistema.
-- ============================================================================

-- NOTA: Si ya existe la tabla anterior, ejecutar primero:
-- DROP TABLE IF EXISTS valoraciones;

-- Crear tabla de valoraciones de productos
CREATE TABLE IF NOT EXISTS valoraciones (
    -- Identificador único de la valoración
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    -- Referencia al usuario que realiza la valoración
    id_usuario INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    
    -- Referencia al producto que se valora
    id_producto INT NOT NULL,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE,
    
    -- Puntuación de 1 a 5 estrellas
    puntuacion INT NOT NULL CHECK (puntuacion >= 1 AND puntuacion <= 5),
    
    -- Comentario/opinión del usuario (puede estar vacío)
    comentario TEXT,
    
    -- Fecha y hora de creación de la valoración
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- Fecha de última actualización (si el usuario edita su valoración)
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Restricción: Un usuario solo puede valorar un producto una sola vez
    UNIQUE KEY unique_usuario_producto (id_usuario, id_producto),
    
    -- Índices para optimizar búsquedas
    INDEX idx_usuario (id_usuario),
    INDEX idx_producto (id_producto),
    INDEX idx_puntuacion (puntuacion),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

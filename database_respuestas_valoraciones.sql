-- ============================================================================
-- database_respuestas_valoraciones.sql
-- Script para crear tabla de respuestas a valoraciones
-- Permite que trabajadores respondan a las reseñas de clientes
-- ============================================================================

CREATE TABLE IF NOT EXISTS respuestas_valoraciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_valoracion INT NOT NULL,
    FOREIGN KEY (id_valoracion) REFERENCES valoraciones(id) ON DELETE CASCADE,
    respuesta TEXT NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_valoracion (id_valoracion),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para notificaciones de respuestas
CREATE TABLE IF NOT EXISTS notificaciones_respuestas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    id_valoracion INT NOT NULL,
    FOREIGN KEY (id_valoracion) REFERENCES valoraciones(id) ON DELETE CASCADE,
    leida BOOLEAN DEFAULT FALSE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (id_usuario),
    INDEX idx_leida (leida),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

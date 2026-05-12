-- Tablas para el programa de fidelidad y personalizador de productos
-- Ejecutar este archivo en tu base de datos

-- Tabla: loyalt_users (puntos de usuarios)
CREATE TABLE IF NOT EXISTS `loyalty_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL UNIQUE,
  `puntos` int(11) NOT NULL DEFAULT 0,
  `nivel` enum('bronce','silver','gold') NOT NULL DEFAULT 'bronce',
  `total_canjeado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: loyalt_transactions (historial de puntos)
CREATE TABLE IF NOT EXISTS `loyalty_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `tipo` enum('ganado','canjeado') NOT NULL,
  `puntos` int(11) NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: loyalt_rewards (recompensas)
CREATE TABLE IF NOT EXISTS `loyalty_rewards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `puntos_necesarios` int(11) NOT NULL,
  `descuento_eur` decimal(6,2) DEFAULT NULL,
  `producto_gratis_id` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`producto_gratis_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: product_extras (extras disponibles)
CREATE TABLE IF NOT EXISTS `product_extras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `precio_adicional` decimal(6,2) NOT NULL DEFAULT 0.00,
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: product_allergens (alérgenos)
CREATE TABLE IF NOT EXISTS `product_allergens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `icono` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: producto_extras (relación producto-extra)
CREATE TABLE IF NOT EXISTS `producto_extras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) NOT NULL,
  `id_extra` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_extra`) REFERENCES `product_extras` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: producto_allergens (relación producto-alérgeno)
CREATE TABLE IF NOT EXISTS `producto_allergens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) NOT NULL,
  `id_allergen` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_allergen`) REFERENCES `product_allergens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos iniciales: Recompensas
INSERT INTO `loyalty_rewards` (`nombre`, `descripcion`, `puntos_necesarios`, `descuento_eur`, `producto_gratis_id`, `activo`) VALUES
('Descuento 5€', 'Canjea por 5€ de descuento en tu siguiente pedido', 200, 5.00, NULL, 1),
('Hotdog Gratis', 'Recibe un hotdog clásico gratis', 300, NULL, 4, 1),
('Descuento 10€', 'Canjea por 10€ de descuento', 500, 10.00, NULL, 1);

-- Datos iniciales: Extras
INSERT INTO `product_extras` (`nombre`, `precio_adicional`, `activo`) VALUES
('Queso extra', 1.00, 1),
('Bacon', 1.50, 1),
('Cebolla caramelizada', 0.80, 1),
('Jalapeños', 0.50, 1),
('Salsa BBQ extra', 0.60, 1);

-- Datos iniciales: Alérgenos
INSERT INTO `product_allergens` (`nombre`, `icono`) VALUES
('Gluten', '🌾'),
('Lácteos', '🥛'),
('Huevos', '🥚'),
('Soja', '🫘'),
('Frutos secos', '🥜'),
('Pescado', '🐟'),
('Mariscos', '🦐'),
('Sésamo', '🌱');

-- Datos iniciales: Relación producto-extras (ejemplo: Hotdog BBQ con Queso extra, Bacon, Salsa BBQ extra)
INSERT INTO `producto_extras` (`id_producto`, `id_extra`) VALUES
(3, 1), (3, 2), (3, 5),
(4, 1), (4, 2),
(5, 2), (5, 4);

-- Datos iniciales: Relación producto-alérgenos (ejemplo)
INSERT INTO `producto_allergens` (`id_producto`, `id_allergen`) VALUES
(1, 2), (3, 2), (4, 2), (5, 4);

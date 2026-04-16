-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-04-2026 a las 10:18:01
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `zyma`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carta_ingredientes`
--

CREATE TABLE `carta_ingredientes` (
  `id_producto` int(11) NOT NULL,
  `id_ingrediente` int(11) NOT NULL,
  `cantidad` decimal(6,3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carta_ingredientes`
--

INSERT INTO `carta_ingredientes` (`id_producto`, `id_ingrediente`, `cantidad`) VALUES
(1, 1, 0.200),
(1, 2, 1.000),
(1, 3, 0.050),
(1, 4, 0.050),
(1, 5, 0.030),
(2, 6, 0.300),
(3, 7, 1.000),
(4, 8, 1.000),
(5, 9, 0.150),
(5, 10, 0.100);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_producto`
--

CREATE TABLE `categorias_producto` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `id_restaurante` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias_producto`
--

INSERT INTO `categorias_producto` (`id_categoria`, `nombre`, `id_restaurante`) VALUES
(2, 'Bebidas', 1),
(1, 'Platos', 1),
(3, 'Postres', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente_categorias_producto`
--

CREATE TABLE `cliente_categorias_producto` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente_categorias_producto`
--

INSERT INTO `cliente_categorias_producto` (`id_categoria`, `nombre`) VALUES
(1, 'Platos'),
(2, 'Bebidas'),
(3, 'Postres');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente_menu_carta`
--

CREATE TABLE `cliente_menu_carta` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(6,2) NOT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente_menu_carta`
--

INSERT INTO `cliente_menu_carta` (`id`, `nombre`, `descripcion`, `precio`, `imagen_url`, `activo`) VALUES
(1, 'Hamburguesa Clásica', 'Carne y queso', 12.50, NULL, 1),
(2, 'Pizza Margarita', 'Tomate y mozzarella', 14.00, NULL, 1),
(3, 'Agua Mineral', '500ml', 2.00, NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente_restaurantes`
--

CREATE TABLE `cliente_restaurantes` (
  `id_restaurante` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `horario_apertura` time DEFAULT NULL,
  `horario_cierre` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente_restaurantes`
--

INSERT INTO `cliente_restaurantes` (`id_restaurante`, `nombre`, `horario_apertura`, `horario_cierre`) VALUES
(1, 'Restaurante Zyma', '12:00:00', '23:30:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cookie_consentimientos`
--

CREATE TABLE `cookie_consentimientos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `policy_version` varchar(32) NOT NULL,
  `estado` enum('accepted','customized','rejected') NOT NULL,
  `essential` tinyint(1) NOT NULL DEFAULT 1,
  `analytics` tinyint(1) NOT NULL DEFAULT 0,
  `marketing` tinyint(1) NOT NULL DEFAULT 0,
  `consented_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cookie_consentimientos`
--

INSERT INTO `cookie_consentimientos` (`id`, `id_usuario`, `policy_version`, `estado`, `essential`, `analytics`, `marketing`, `consented_at`, `updated_at`) VALUES
(1, 12, '2026-03-17', 'rejected', 1, 0, 0, '2026-04-14 09:20:53', '2026-04-14 09:20:53'),
(32, 7, '2026-03-17', 'rejected', 1, 0, 0, '2026-04-14 09:58:38', '2026-04-14 09:58:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_pedido_historial`
--

CREATE TABLE `estados_pedido_historial` (
  `id` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `estado` enum('pendiente','preparando','listo','entregado','cancelado') NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados_pedido_historial`
--

INSERT INTO `estados_pedido_historial` (`id`, `id_pedido`, `estado`, `fecha`) VALUES
(1, 1, 'pendiente', '2026-01-30 07:37:05'),
(2, 1, 'preparando', '2026-01-30 07:37:05'),
(3, 1, 'entregado', '2026-01-30 07:37:05'),
(4, 2, 'pendiente', '2026-01-30 07:37:05'),
(5, 2, 'preparando', '2026-01-30 07:37:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_producto`
--

CREATE TABLE `imagenes_producto` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `es_principal` tinyint(1) DEFAULT 0,
  `orden` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `imagenes_producto`
--

INSERT INTO `imagenes_producto` (`id`, `id_producto`, `url`, `es_principal`, `orden`) VALUES
(1, 1, 'hamburguesa.jpg', 1, 1),
(2, 2, 'pizza.jpg', 1, 1),
(3, 5, 'tarta.jpg', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidencias_clientes`
--

CREATE TABLE `incidencias_clientes` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `asunto` varchar(120) NOT NULL,
  `categoria` varchar(40) NOT NULL DEFAULT 'general',
  `prioridad` varchar(20) NOT NULL DEFAULT 'media',
  `descripcion` text NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'abierta',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingredientes`
--

CREATE TABLE `ingredientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unidad` varchar(20) NOT NULL,
  `stock_minimo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ingredientes`
--

INSERT INTO `ingredientes` (`id`, `nombre`, `cantidad`, `unidad`, `stock_minimo`, `created_at`, `updated_at`) VALUES
(1, 'Patatas', 1000.00, 'gr', 200.00, '2026-02-10 01:38:27', '2026-02-10 01:38:27'),
(2, 'Queso Cheddar', 500.00, 'gr', 100.00, '2026-02-10 01:38:27', '2026-02-10 08:44:46'),
(3, 'Jalapeños', 50.00, 'unidades', 10.00, '2026-02-10 01:38:27', '2026-02-10 01:38:27'),
(4, 'Salchicha Hotdog', 200.00, 'unidades', 50.00, '2026-02-10 01:38:27', '2026-02-10 01:38:27'),
(5, 'Pan Hotdog', 150.00, 'unidades', 30.00, '2026-02-10 01:38:27', '2026-02-10 01:38:27'),
(6, 'Salsa BBQ', 2000.00, 'ml', 500.00, '2026-02-10 01:38:27', '2026-02-10 01:38:27'),
(7, 'Cebolla', 100.00, 'unidades', 20.00, '2026-02-10 01:38:27', '2026-02-10 01:38:27'),
(8, 'Mostaza', 1000.00, 'ml', 200.00, '2026-02-10 01:38:27', '2026-02-10 01:38:27'),
(9, 'Mayonesa Vegana', 800.00, 'ml', 150.00, '2026-02-10 01:38:27', '2026-02-10 01:38:27'),
(10, 'Pepinillos', 80.00, 'unidades', 15.00, '2026-02-10 01:38:27', '2026-02-10 01:38:27'),
(11, 'Refresco Cola', 5000.00, 'ml', 1000.00, '2026-02-10 01:38:27', '2026-02-10 01:38:27'),
(12, 'Agua Mineral', 10000.00, 'ml', 2000.00, '2026-02-10 01:38:27', '2026-02-10 01:38:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_stock`
--

CREATE TABLE `logs_stock` (
  `id` int(11) NOT NULL,
  `id_ingrediente` int(11) NOT NULL,
  `cambio` int(11) NOT NULL,
  `motivo` enum('venta','ajuste','merma','reposicion') NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `logs_stock`
--

INSERT INTO `logs_stock` (`id`, `id_ingrediente`, `cambio`, `motivo`, `fecha`) VALUES
(1, 1, -1, 'venta', '2026-01-30 07:37:05'),
(2, 9, -1, 'venta', '2026-01-30 07:37:05'),
(3, 7, -1, 'venta', '2026-01-30 07:37:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menu_carta`
--

CREATE TABLE `menu_carta` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(6,2) NOT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `id_categoria` int(11) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `menu_carta`
--

INSERT INTO `menu_carta` (`id`, `nombre`, `descripcion`, `precio`, `imagen_url`, `id_categoria`, `activo`, `stock`) VALUES
(1, 'Hamburguesa Clásica', 'Carne, lechuga, tomate y queso', 12.50, NULL, 1, 1, 20),
(2, 'Pizza Margarita', 'Tomate y mozzarella', 14.00, NULL, 1, 1, 15),
(3, 'Agua Mineral', 'Botella 500ml', 2.00, NULL, 2, 1, 50),
(4, 'Refresco', 'Cola / Naranja / Limón', 3.00, NULL, 2, 1, 40),
(5, 'Tarta de Chocolate', 'Con helado de vainilla', 6.50, NULL, 3, 1, 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas`
--

CREATE TABLE `mesas` (
  `id_mesa` int(11) NOT NULL,
  `numero_mesa` varchar(10) NOT NULL,
  `id_restaurante` int(11) NOT NULL,
  `activa` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mesas`
--

INSERT INTO `mesas` (`id_mesa`, `numero_mesa`, `id_restaurante`, `activa`) VALUES
(1, 'A1', 1, 1),
(2, 'A2', 1, 1),
(3, 'B1', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `mensaje` text NOT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `id_usuario`, `mensaje`, `leida`, `fecha`) VALUES
(1, 1, 'Tu pedido ha sido entregado', 1, '2026-01-30 07:37:05'),
(2, 2, 'Tu pedido está en preparación', 0, '2026-01-30 07:37:05'),
(0, 4, 'Tu pedido #0 está en preparación.', 1, '2026-02-10 22:42:09'),
(0, 4, 'Tu pedido #0 está listo para recoger.', 1, '2026-02-10 22:42:15'),
(0, 3, 'Nuevo pedido #0 de hola por 1,50€', 1, '2026-02-11 00:20:48'),
(0, 4, 'Tu pedido #0 ha sido cancelado.', 1, '2026-02-11 02:37:13'),
(0, 9, 'Nuevo pedido #0 de marc por 7,50?', 1, '2026-02-13 11:30:19'),
(0, 9, 'Nuevo pedido #0 de marc por 14,99?', 0, '2026-02-13 11:37:30'),
(0, 4, 'Tu pedido #0 ha sido cancelado.', 0, '2026-02-13 11:37:45'),
(0, 12, 'Nuevo pedido #0 de janbruguefernandez por 9,00?', 0, '2026-02-20 08:52:28'),
(0, 12, 'Nuevo pedido #1 de haMBY por 7,50 EUR', 0, '2026-03-02 13:36:00'),
(0, 15, 'Nuevo pedido #2 de pau1 por 9,00 EUR', 0, '2026-03-06 08:12:18'),
(0, 12, 'Nuevo pedido #3 de haMBY por 7,49 EUR', 0, '2026-03-17 12:32:45'),
(0, 12, 'Tu pedido #3 ha sido cancelado.', 0, '2026-04-14 09:47:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones_respuestas`
--

CREATE TABLE `notificaciones_respuestas` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_valoracion` int(11) NOT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificaciones_respuestas`
--

INSERT INTO `notificaciones_respuestas` (`id`, `id_usuario`, `id_valoracion`, `leida`, `fecha_creacion`) VALUES
(1, 12, 8, 0, '2026-02-27 09:23:35'),
(2, 14, 10, 1, '2026-02-27 09:23:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `metodo` enum('efectivo','tarjeta','bizum','online') NOT NULL,
  `monto` decimal(6,2) NOT NULL,
  `estado` enum('pendiente','pagado','rechazado') DEFAULT 'pendiente',
  `fecha_pago` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pago`, `id_pedido`, `metodo`, `monto`, `estado`, `fecha_pago`) VALUES
(1, 1, 'tarjeta', 17.50, 'pagado', '2026-01-30 07:37:05'),
(2, 2, 'efectivo', 6.50, 'pendiente', '2026-01-30 07:37:05'),
(0, 0, 'tarjeta', 1.50, 'pagado', '2026-02-11 00:20:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token_hash`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 12, 'ba26f0deead6df29832549ee9798a304a6b19999f3c3b00067b878d6e22c8bf8', '2026-03-06 09:25:17', NULL, '2026-03-06 08:25:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_mesa` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  `estado` enum('pendiente','preparando','listo','entregado','cancelado') DEFAULT 'pendiente',
  `total` decimal(6,2) NOT NULL,
  `metodo_pago` varchar(30) DEFAULT 'efectivo',
  `notas_cliente` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_mesa`, `id_usuario`, `fecha_hora`, `estado`, `total`, `metodo_pago`, `notas_cliente`, `created_at`) VALUES
(0, 0, 4, '2026-02-10 03:09:17', 'cancelado', 7.50, 'qr', '', '2026-02-10 02:09:17'),
(0, 0, 4, '2026-02-10 03:09:19', 'cancelado', 7.50, 'qr', '', '2026-02-10 02:09:19'),
(0, 0, 4, '2026-02-10 03:09:20', 'cancelado', 7.50, 'qr', '', '2026-02-10 02:09:20'),
(0, 0, 4, '2026-02-10 09:04:36', 'cancelado', 7.50, 'qr', '', '2026-02-10 08:04:36'),
(0, 0, 4, '2026-02-10 09:05:01', 'cancelado', 3.50, 'qr', '', '2026-02-10 08:05:01'),
(0, 0, 3, '2026-02-10 13:03:07', 'cancelado', 3.50, 'qr', '', '2026-02-10 12:03:07'),
(0, 0, 3, '2026-02-10 22:34:36', 'cancelado', 7.50, 'qr', '', '2026-02-10 21:34:36'),
(0, 0, 3, '2026-02-10 22:41:47', 'cancelado', 7.50, 'qr', '', '2026-02-10 21:41:47'),
(0, 0, 3, '2026-02-11 00:20:48', 'cancelado', 1.50, 'tarjeta', '', '2026-02-10 23:20:48'),
(0, 0, 9, '2026-02-13 11:30:19', 'cancelado', 7.50, 'qr', '', '2026-02-13 10:30:19'),
(0, 0, 9, '2026-02-13 11:37:30', 'cancelado', 14.99, 'qr', '', '2026-02-13 10:37:30'),
(0, 0, 12, '2026-02-20 08:52:28', 'cancelado', 9.00, 'qr', '', '2026-02-20 07:52:28'),
(1, 0, 12, '2026-03-02 13:36:00', 'pendiente', 7.50, 'tarjeta', '', '2026-03-02 12:36:00'),
(2, 0, 15, '2026-03-06 08:12:18', 'pendiente', 9.00, 'tarjeta', '', '2026-03-06 07:12:18'),
(3, 0, 12, '2026-03-17 12:32:45', 'cancelado', 7.49, 'tarjeta', '', '2026-03-17 11:32:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_items`
--

CREATE TABLE `pedido_items` (
  `id` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL CHECK (`cantidad` > 0),
  `precio_unitario` decimal(6,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido_items`
--

INSERT INTO `pedido_items` (`id`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`) VALUES
(1, 1, 1, 1, 12.50),
(2, 1, 3, 1, 2.00),
(3, 2, 5, 1, 6.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `precio`, `imagen`, `created_at`) VALUES
(1, 'Nachos con Queso', 6.00, 'assets/nachos.png', '2026-02-10 01:53:24'),
(2, 'Patatas Fritas', 3.50, 'assets/fries.png', '2026-02-10 01:53:24'),
(3, 'Hotdog BBQ', 7.50, 'assets/bbq_hotdog.png', '2026-02-10 01:53:24'),
(4, 'Hotdog Clásico', 5.99, 'assets/hotdog.png', '2026-02-10 01:53:24'),
(5, 'Hotdog Vegano', 6.60, 'assets/vegan-hotdog.png', '2026-02-10 01:53:24'),
(6, 'Refresco Cola', 2.00, 'assets/soda.png', '2026-02-10 01:53:24'),
(7, 'Agua Mineral', 1.00, 'assets/water.png', '2026-02-10 01:53:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_ingredientes`
--

CREATE TABLE `producto_ingredientes` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `ingrediente_id` int(11) NOT NULL,
  `cantidad_necesaria` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto_ingredientes`
--

INSERT INTO `producto_ingredientes` (`id`, `producto_id`, `ingrediente_id`, `cantidad_necesaria`) VALUES
(9, 1, 1, 150.00),
(10, 1, 2, 50.00),
(11, 1, 3, 10.00),
(12, 2, 1, 200.00),
(13, 3, 4, 1.00),
(14, 3, 5, 1.00),
(15, 3, 6, 30.00),
(16, 3, 7, 1.00),
(17, 4, 4, 1.00),
(18, 4, 5, 1.00),
(19, 4, 8, 10.00),
(20, 4, 7, 1.00),
(21, 5, 4, 1.00),
(22, 5, 5, 1.00),
(23, 5, 11, 15.00),
(24, 5, 12, 5.00),
(25, 6, 9, 330.00),
(26, 7, 10, 500.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resenas`
--

CREATE TABLE `resenas` (
  `id_resena` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `calificacion` tinyint(4) DEFAULT NULL CHECK (`calificacion` between 1 and 5),
  `comentario` text DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `resenas`
--

INSERT INTO `resenas` (`id_resena`, `id_usuario`, `id_pedido`, `calificacion`, `comentario`, `fecha`) VALUES
(1, 1, 1, 5, 'Todo perfecto', '2026-01-30 07:37:05'),
(2, 2, 2, 4, 'Muy bueno', '2026-01-30 07:37:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas_valoraciones`
--

CREATE TABLE `respuestas_valoraciones` (
  `id` int(11) NOT NULL,
  `id_valoracion` int(11) NOT NULL,
  `respuesta` text NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `respuestas_valoraciones`
--

INSERT INTO `respuestas_valoraciones` (`id`, `id_valoracion`, `respuesta`, `fecha_creacion`) VALUES
(1, 8, 'tonto', '2026-02-27 09:23:35'),
(2, 10, 'sdf', '2026-02-27 09:23:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `restaurantes`
--

CREATE TABLE `restaurantes` (
  `id_restaurante` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `horario_apertura` time DEFAULT NULL,
  `horario_cierre` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `restaurantes`
--

INSERT INTO `restaurantes` (`id_restaurante`, `nombre`, `direccion`, `telefono`, `horario_apertura`, `horario_cierre`) VALUES
(1, 'Restaurante Zyma', 'Calle Ejemplo 123', '900123456', '12:00:00', '23:30:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`) VALUES
(2, 'admin'),
(1, 'cliente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(60) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `worker_code` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bloqueado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password_hash`, `worker_code`, `created_at`, `bloqueado`) VALUES
(3, 'hola', 'hola@gmail.com', '$2y$10$uZoaNuQ1.bi.pd9sQ.AY.OWWj6YvTFEAQBoUVmaNOzH87gAetylNW', NULL, '2026-02-03 11:34:22', 0),
(4, 'trabajador', 'trabajador@gmail.com', '$2y$10$n6L6M079LJrxlJGJgCbgXuKBNxKmyiHRTYv2OG3QC.bWrVOqcJrIq', 'TRAB001', '2026-02-09 19:48:30', 0),
(7, NULL, 'admin@gmail.com', '$2y$10$e28HCvujXfCPbYhNPq1L0eq2gNK.b97EtM1qp82qPbk.LHtk9qiHm', 'ADMIN', '2026-02-11 07:30:44', 0),
(8, 'Cristan', 'cristian@gmail.com', '$2y$10$TjfIjiANH6dcoYzRNPZCHOYxD/g8NnJoX8O/4LUMTaiPhXaZD5nQe', NULL, '2026-02-11 12:06:11', 0),
(9, NULL, 'marc@gmail.com', '$2y$10$SKkjqGDdYFLPfpRL2c4ewOLBYYpaaTYyqR86TbEBORFOXmDTBigHe', NULL, '2026-02-13 10:29:31', 0),
(10, NULL, 'bruno@gmail.com', '$2y$10$o4WaeFjPKRGhxbthhXuiueEj.gzkw6o.MLBWOXfx5qFZNvnCXWxWS', NULL, '2026-02-17 11:15:41', 0),
(11, NULL, 'eliinchina@gmail.com', '$2y$10$mFLI0arqth6nz5tFF5z2LOhMOhQBAqLNu1fK4GKzapid1IXS1Qd7C', NULL, '2026-02-17 11:27:57', 0),
(12, 'jan', 'janbruguefernandez@gmail.com', '$2y$10$tt7RXQTIq8XtGqDOxgUZC.hMCct3hB15YBzvP1mg0CdiSs8DF9RwS', NULL, '2026-02-20 07:12:16', 0),
(13, NULL, 'janbrugue@inslc.cat', '$2y$10$lktzct9nITeg6AGxyokb.OUGBosTcH8wjFBW14XrwSeIff/9QHwc2', 'ADMIN', '2026-02-27 08:12:39', 0),
(14, NULL, 'e6blpqwhku@outlook.com', '$2y$10$vvzsNctCUnNDhAKVPzMlIugTk/BsO8FX.gqqNY4aC3xlv.xd8RkuS', 'TRAB001', '2026-02-27 08:14:15', 0),
(15, 'Pau', 'pau1@gmail.com', '$2y$10$WTUBwk2NHAjEX.yNLdjS.ORX9H/Z3tg3y1tQxwPnNlgjQBXyuATLy', NULL, '2026-03-06 07:10:40', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `valoraciones`
--

CREATE TABLE `valoraciones` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `puntuacion` int(11) NOT NULL CHECK (`puntuacion` >= 1 and `puntuacion` <= 5),
  `comentario` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `valoraciones`
--

INSERT INTO `valoraciones` (`id`, `id_usuario`, `id_producto`, `puntuacion`, `comentario`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(4, 12, 3, 3, 'hola', '2026-02-20 08:50:48', '2026-03-02 13:27:18'),
(5, 12, 4, 2, 'bondia', '2026-02-20 08:50:58', '2026-02-27 12:01:51'),
(6, 12, 5, 3, 'hola', '2026-02-20 08:51:15', '2026-02-20 09:19:54'),
(7, 12, 7, 3, 'bon dia', '2026-02-20 08:51:27', '2026-03-02 13:27:07'),
(8, 12, 6, 4, '', '2026-02-20 08:51:37', '2026-02-20 08:51:37'),
(9, 12, 2, 4, '', '2026-02-20 09:20:00', '2026-02-20 09:20:00'),
(10, 14, 1, 2, 'asdcf', '2026-02-27 09:23:48', '2026-02-27 09:23:48'),
(11, 14, 5, 3, 'asdcd', '2026-02-27 09:28:45', '2026-02-27 09:28:45'),
(12, 14, 3, 3, 'me da mucho asco', '2026-02-27 10:11:17', '2026-03-03 10:49:58'),
(13, 14, 7, 2, 'nanit', '2026-03-03 10:51:00', '2026-03-03 10:51:00'),
(14, 14, 6, 2, 'ed', '2026-03-03 10:51:09', '2026-03-03 10:51:09'),
(15, 14, 4, 3, 'wsdsssfc', '2026-03-03 10:52:25', '2026-03-03 10:53:34'),
(16, 15, 3, 1, 'malo', '2026-03-06 08:13:58', '2026-03-06 08:13:58');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carta_ingredientes`
--
ALTER TABLE `carta_ingredientes`
  ADD PRIMARY KEY (`id_producto`,`id_ingrediente`),
  ADD KEY `id_ingrediente` (`id_ingrediente`);

--
-- Indices de la tabla `categorias_producto`
--
ALTER TABLE `categorias_producto`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre` (`nombre`,`id_restaurante`),
  ADD KEY `id_restaurante` (`id_restaurante`);

--
-- Indices de la tabla `cliente_categorias_producto`
--
ALTER TABLE `cliente_categorias_producto`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `cliente_menu_carta`
--
ALTER TABLE `cliente_menu_carta`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cliente_restaurantes`
--
ALTER TABLE `cliente_restaurantes`
  ADD PRIMARY KEY (`id_restaurante`);

--
-- Indices de la tabla `cookie_consentimientos`
--
ALTER TABLE `cookie_consentimientos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_cookie_user` (`id_usuario`),
  ADD KEY `idx_cookie_policy` (`policy_version`);

--
-- Indices de la tabla `estados_pedido_historial`
--
ALTER TABLE `estados_pedido_historial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pedido` (`id_pedido`);

--
-- Indices de la tabla `incidencias_clientes`
--
ALTER TABLE `incidencias_clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `ingredientes`
--
ALTER TABLE `ingredientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `notificaciones_respuestas`
--
ALTER TABLE `notificaciones_respuestas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_valoracion` (`id_valoracion`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_leida` (`leida`),
  ADD KEY `idx_fecha` (`fecha_creacion`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_token_hash` (`token_hash`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `producto_ingredientes`
--
ALTER TABLE `producto_ingredientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `ingrediente_id` (`ingrediente_id`);

--
-- Indices de la tabla `respuestas_valoraciones`
--
ALTER TABLE `respuestas_valoraciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_valoracion` (`id_valoracion`),
  ADD KEY `idx_fecha` (`fecha_creacion`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `valoraciones`
--
ALTER TABLE `valoraciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_producto` (`id_usuario`,`id_producto`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_producto` (`id_producto`),
  ADD KEY `idx_puntuacion` (`puntuacion`),
  ADD KEY `idx_fecha` (`fecha_creacion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cookie_consentimientos`
--
ALTER TABLE `cookie_consentimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `incidencias_clientes`
--
ALTER TABLE `incidencias_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ingredientes`
--
ALTER TABLE `ingredientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `notificaciones_respuestas`
--
ALTER TABLE `notificaciones_respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `producto_ingredientes`
--
ALTER TABLE `producto_ingredientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `respuestas_valoraciones`
--
ALTER TABLE `respuestas_valoraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `valoraciones`
--
ALTER TABLE `valoraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cookie_consentimientos`
--
ALTER TABLE `cookie_consentimientos`
  ADD CONSTRAINT `fk_cookie_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `incidencias_clientes`
--
ALTER TABLE `incidencias_clientes`
  ADD CONSTRAINT `fk_incidencias_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones_respuestas`
--
ALTER TABLE `notificaciones_respuestas`
  ADD CONSTRAINT `notificaciones_respuestas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notificaciones_respuestas_ibfk_2` FOREIGN KEY (`id_valoracion`) REFERENCES `valoraciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `producto_ingredientes`
--
ALTER TABLE `producto_ingredientes`
  ADD CONSTRAINT `producto_ingredientes_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `producto_ingredientes_ibfk_2` FOREIGN KEY (`ingrediente_id`) REFERENCES `ingredientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `respuestas_valoraciones`
--
ALTER TABLE `respuestas_valoraciones`
  ADD CONSTRAINT `respuestas_valoraciones_ibfk_1` FOREIGN KEY (`id_valoracion`) REFERENCES `valoraciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `valoraciones`
--
ALTER TABLE `valoraciones`
  ADD CONSTRAINT `valoraciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `valoraciones_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

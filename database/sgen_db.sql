-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-12-2025 a las 17:12:29
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
-- Base de datos: `sgen_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bajas_inventario`
--

CREATE TABLE `bajas_inventario` (
  `id` int(11) NOT NULL,
  `inventario_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `motivo` text DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora_acciones`
--

CREATE TABLE `bitacora_acciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL COMMENT 'El usuario que hizo la acción',
  `username` varchar(50) NOT NULL COMMENT 'El nombre del usuario en ese momento',
  `accion` varchar(255) NOT NULL COMMENT 'Descripción de lo que hizo',
  `enlace_tipo` varchar(50) DEFAULT NULL COMMENT 'Tipo de enlace (ej: soporte, equipo)',
  `enlace_id` int(11) DEFAULT NULL COMMENT 'ID del objeto enlazado (ej: 12)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `bitacora_acciones`
--

INSERT INTO `bitacora_acciones` (`id`, `usuario_id`, `username`, `accion`, `enlace_tipo`, `enlace_id`, `created_at`) VALUES
(163, 1, 'admin', 'Creó al usuario Jonas', 'usuario', 35, '2025-11-24 01:38:49'),
(164, 1, 'admin', 'Creó al usuario Jose', 'usuario', 36, '2025-11-24 01:38:59'),
(165, 1, 'admin', 'Creó al empleado Jonas Mendoza', 'empleado', 31, '2025-11-24 01:39:34'),
(166, 1, 'admin', 'Creó al empleado Jose Escalona', 'empleado', 32, '2025-11-24 01:40:08'),
(167, 1, 'admin', 'Creó el departamento Departamento de Informática', 'departamento', 21, '2025-11-24 01:40:57'),
(168, 1, 'admin', 'Creó el departamento Departamento de Gestión de Riesgos', 'departamento', 22, '2025-11-24 01:41:48'),
(169, 1, 'admin', 'Creó el departamento Departamento Catastro', 'departamento', 23, '2025-11-24 01:42:30'),
(170, 1, 'admin', 'Actualizó al empleado Jonas Mendoza', 'empleado', 31, '2025-11-24 01:42:52'),
(171, 1, 'admin', 'Actualizó al empleado Jose Escalona', 'empleado', 32, '2025-11-24 01:43:00'),
(172, 1, 'admin', 'Creó el equipo (Código: 01)', 'equipo', 54, '2025-11-24 01:48:09'),
(173, 1, 'admin', 'Actualizó el equipo (Código: 01)', 'equipo', 54, '2025-11-24 01:48:38'),
(174, 1, 'admin', 'Creó el equipo (Código: 02)', 'equipo', 55, '2025-11-24 01:54:04'),
(175, 1, 'admin', 'Registró nuevo ítem \'Disco Hdd 500gb\'', 'inventario', NULL, '2025-11-24 01:56:21'),
(176, 1, 'admin', 'Registró nuevo ítem \'Memoria ram 16gb\'', 'inventario', NULL, '2025-11-24 01:58:09'),
(177, 1, 'admin', 'Eliminó el departamento Departamento de Gestión de Riesgos', 'departamento', 22, '2025-11-24 01:58:40'),
(178, 1, 'admin', 'Transfirió 10 unidades del ítem #5', 'inventario', 5, '2025-11-24 02:00:12'),
(179, 1, 'admin', 'Transfirió 10 unidades del ítem #6', 'inventario', 6, '2025-11-24 02:00:42'),
(180, 1, 'admin', 'Transfirió 10 unidades del ítem #6', 'inventario', 6, '2025-11-24 02:01:00'),
(181, 1, 'admin', 'Transfirió 10 unidades del ítem #5', 'inventario', 5, '2025-11-24 02:01:23'),
(182, 1, 'admin', 'Actualizó al empleado Jose Escalona', 'empleado', 32, '2025-11-24 02:36:22'),
(183, 1, 'admin', 'Actualizó al empleado Jose Escalona', 'empleado', 32, '2025-11-24 02:36:26'),
(184, 1, 'admin', 'Actualizó al empleado Jose Escalona', 'empleado', 32, '2025-11-24 02:38:40'),
(185, 36, 'Jose', 'Creó ticket #51', 'soporte', 51, '2025-11-24 02:53:54'),
(186, 1, 'admin', 'Registró nuevo ítem \'Tarjeta Madre\'', 'inventario', NULL, '2025-11-24 13:23:26'),
(187, 1, 'admin', 'Transfirió 3 unidades del ítem #7', 'inventario', 7, '2025-11-24 13:23:58'),
(188, 1, 'admin', 'Transfirió 3 unidades del ítem #7', 'inventario', 7, '2025-11-24 13:24:16'),
(189, 1, 'admin', 'Transfirió 1 unidades del ítem #7', 'inventario', 7, '2025-11-24 13:27:49'),
(190, 1, 'admin', 'Creó el equipo (Código: 988214)', 'equipo', 56, '2025-11-24 15:02:07'),
(191, 1, 'admin', 'Creó el equipo (Código: 19841241)', 'equipo', 57, '2025-11-24 15:27:09'),
(192, 1, 'admin', 'Creó el equipo (Código: 41241241)', 'equipo', 58, '2025-11-24 15:29:41'),
(193, 1, 'admin', 'Asignó equipo 01 al departamento #21', 'departamento', 21, '2025-11-24 20:13:03'),
(194, 1, 'admin', 'Actualizó al empleado Jonas Mendoza', 'empleado', 31, '2025-11-24 20:13:29'),
(195, 1, 'admin', 'Creó el equipo (Código: 03)', 'equipo', 59, '2025-11-24 21:24:07'),
(196, 1, 'admin', 'Creó el equipo (Código: 04)', 'equipo', 60, '2025-11-24 23:04:51'),
(197, 1, 'admin', 'Creó el equipo (Código: 05)', 'equipo', 61, '2025-11-24 23:13:02'),
(198, 1, 'admin', 'Creó el equipo (Código: 12422421412414)', 'equipo', 63, '2025-11-24 23:38:32'),
(199, 1, 'admin', 'Actualizó el equipo (Código: 12422421412414)', 'equipo', 63, '2025-11-24 23:39:52'),
(200, 1, 'admin', 'Creó el equipo (Código: 06)', 'equipo', 64, '2025-11-24 23:53:04'),
(201, 1, 'admin', 'Creó el equipo (Código: 42142241)', 'equipo', 65, '2025-11-25 00:01:06'),
(202, 1, 'admin', 'Creó el equipo (Código: 41421)', 'equipo', 66, '2025-11-25 00:10:18'),
(203, 1, 'admin', 'Creó el equipo (Código: 421412)', 'equipo', 67, '2025-11-25 00:26:03'),
(204, 1, 'admin', 'Creó el equipo (Código: 142121)', 'equipo', 68, '2025-11-25 02:00:13'),
(205, 1, 'admin', 'Actualizó el equipo (Código: 142121)', 'equipo', 68, '2025-11-25 02:00:40'),
(206, 1, 'admin', 'Actualizó el equipo (Código: 142121)', 'equipo', 68, '2025-11-25 02:09:22'),
(207, 1, 'admin', 'Actualizó el equipo (Código: 142121)', 'equipo', 68, '2025-11-25 02:09:29'),
(208, 1, 'admin', 'Actualizó el equipo (Código: 142121)', 'equipo', 68, '2025-11-25 02:09:34'),
(209, 1, 'admin', 'Actualizó el equipo (Código: 142121)', 'equipo', 68, '2025-11-25 02:10:15'),
(210, 1, 'admin', 'Actualizó al empleado Jonas Mendoza', 'empleado', 31, '2025-11-25 02:26:23'),
(211, 1, 'admin', 'Actualizó al empleado Jonas Mendoza', 'empleado', 31, '2025-11-25 02:26:47'),
(212, 1, 'admin', 'Actualizó al empleado Jonas Mendoza', 'empleado', 31, '2025-11-25 02:34:28'),
(213, 1, 'admin', 'Actualizó al empleado Jonas Mendoza', 'empleado', 31, '2025-11-25 02:39:01'),
(214, 1, 'admin', 'Actualizó al empleado Jonas Mendoza', 'empleado', 31, '2025-11-25 02:41:21'),
(215, 1, 'admin', 'Actualizó al empleado Jonas Mendoza', 'empleado', 31, '2025-11-25 02:41:36'),
(216, 1, 'admin', 'Creó ticket #52', 'soporte', 52, '2025-11-25 02:52:13'),
(217, 1, 'admin', 'Actualizó al empleado Jonas Mendoza', 'empleado', 31, '2025-11-25 02:58:18'),
(218, 1, 'admin', 'Creó al empleado Cachito Pilito', 'empleado', 33, '2025-11-25 03:07:00'),
(219, 1, 'admin', 'Actualizó al empleado Cachito Pilito', 'empleado', 33, '2025-11-25 03:07:52'),
(220, 1, 'admin', 'Asignó ticket #51 a empleado #31', 'soporte', 51, '2025-11-25 04:20:02'),
(221, 1, 'admin', 'Puso en espera ticket #51', 'soporte', 51, '2025-11-25 04:20:07'),
(222, 1, 'admin', 'Reanudó ticket #51', 'soporte', 51, '2025-11-25 04:20:09'),
(223, 36, 'Jose', 'Creó ticket #53', 'soporte', 53, '2025-11-25 13:09:14'),
(224, 36, 'Jose', 'Creó ticket #54', 'soporte', 54, '2025-11-25 13:17:24'),
(225, 36, 'Jose', 'Eliminó ticket #51', 'soporte', 51, '2025-11-25 13:21:07'),
(226, 36, 'Jose', 'Eliminó ticket #54', 'soporte', 54, '2025-11-25 13:21:09'),
(227, 36, 'Jose', 'Actualizó ticket #53', 'soporte', 53, '2025-11-25 13:24:47'),
(228, 36, 'Jose', 'Creó ticket #55', 'soporte', 55, '2025-11-25 13:25:06'),
(229, 35, 'Jonas', 'Asignó ticket #52 a empleado #31', 'soporte', 52, '2025-11-25 13:35:09'),
(230, 35, 'Jonas', 'Asignó ticket #52 a empleado #31', 'soporte', 52, '2025-11-25 13:35:09'),
(231, 35, 'Jonas', 'Puso en espera ticket #52', 'soporte', 52, '2025-11-25 13:35:12'),
(232, 35, 'Jonas', 'Reanudó ticket #52', 'soporte', 52, '2025-11-25 13:35:13'),
(233, 35, 'Jonas', 'Resolvió ticket #52', 'soporte', 52, '2025-11-25 13:35:23'),
(234, 1, 'admin', 'Eliminó al empleado Cachito Pilito', 'empleado', 33, '2025-11-25 13:36:48'),
(235, 36, 'Jose', 'Creó ticket #56', 'soporte', 56, '2025-11-25 23:53:54'),
(236, 36, 'Jose', 'Creó ticket #57', 'soporte', 57, '2025-11-26 01:12:21'),
(237, 35, 'Jonas', 'Creó ticket #58', 'soporte', 58, '2025-11-26 01:13:16'),
(238, 36, 'Jose', 'Eliminó ticket #57', 'soporte', 57, '2025-11-26 01:15:28'),
(239, 36, 'Jose', 'Actualizó ticket #56', 'soporte', 56, '2025-11-26 01:15:35'),
(240, 36, 'Jose', 'Actualizó ticket #56', 'soporte', 56, '2025-11-26 01:15:35'),
(241, 36, 'Jose', 'Actualizó ticket #56', 'soporte', 56, '2025-11-26 01:15:35'),
(242, 35, 'Jonas', 'Eliminó ticket #58', 'soporte', 58, '2025-11-26 01:17:54'),
(243, 36, 'Jose', 'Actualizó ticket #56', 'soporte', 56, '2025-11-26 01:18:19'),
(244, 36, 'Jose', 'Eliminó ticket #56', 'soporte', 56, '2025-11-26 01:18:23'),
(245, 36, 'Jose', 'Creó ticket #59', 'soporte', 59, '2025-11-26 01:18:42'),
(246, 36, 'Jose', 'Creó ticket #60', 'soporte', 60, '2025-11-26 01:18:53'),
(247, 36, 'Jose', 'Eliminó ticket #60', 'soporte', 60, '2025-11-26 01:18:57'),
(248, 1, 'admin', 'Creó el equipo (Código: 123)', 'equipo', 70, '2025-11-26 01:20:08'),
(249, 1, 'admin', 'Creó el equipo (Código: 2142412)', 'equipo', 71, '2025-11-26 01:21:42'),
(250, 1, 'admin', 'Eliminó el equipo (Código: 988214)', 'equipo', 56, '2025-11-26 01:30:08'),
(251, 1, 'admin', 'Eliminó el equipo (Código: 19841241)', 'equipo', 57, '2025-11-26 01:30:10'),
(252, 1, 'admin', 'Eliminó el equipo (Código: 41241241)', 'equipo', 58, '2025-11-26 01:30:11'),
(253, 1, 'admin', 'Creó el equipo (Código: 121)', 'equipo', 72, '2025-11-26 01:30:35'),
(254, 1, 'admin', 'Creó el equipo (Código: 2124)', 'equipo', 73, '2025-11-26 01:31:19'),
(255, 36, 'Jose', 'Creó ticket #61', 'soporte', 61, '2025-11-26 01:32:33'),
(256, 1, 'admin', 'Asignó ticket #61 a empleado #31', 'soporte', 61, '2025-11-26 02:15:57'),
(257, 1, 'admin', 'Agregó consumo de inventario al ticket #61', 'soporte', 61, '2025-11-26 02:16:02'),
(258, 1, 'admin', 'Agregó consumo de inventario al ticket #61', 'soporte', 61, '2025-11-26 02:17:28'),
(259, 1, 'admin', 'Transfirió 1 unidades del ítem #5', 'inventario', 5, '2025-11-26 14:09:50'),
(260, 1, 'admin', 'Creó al empleado Juan Dominguez', 'empleado', 34, '2025-11-26 14:11:26'),
(261, 1, 'admin', 'Creó al usuario Juan', 'usuario', 37, '2025-11-26 14:11:38'),
(262, 1, 'admin', 'Actualizó al empleado Juan Dominguez', 'empleado', 34, '2025-11-26 14:11:45'),
(263, 1, 'admin', 'Actualizó al empleado Juan Dominguez', 'empleado', 34, '2025-11-26 14:11:45'),
(264, 37, 'Juan', 'Asignó ticket #59 a empleado #34', 'soporte', 59, '2025-11-26 14:12:11'),
(265, 37, 'Juan', 'Asignó ticket #59 a empleado #34', 'soporte', 59, '2025-11-26 14:12:11'),
(266, 1, 'admin', 'Actualizó el equipo (Código: 2124)', 'equipo', 73, '2025-11-26 16:28:01'),
(267, 1, 'admin', 'Actualizó el equipo (Código: 2124)', 'equipo', 73, '2025-11-26 16:28:13'),
(268, 1, 'admin', 'Actualizó al empleado Juan Dominguez', 'empleado', 34, '2025-11-26 16:42:58'),
(269, 1, 'admin', 'Creó al empleado julito alimaña', 'empleado', 35, '2025-11-26 16:43:58'),
(270, 1, 'admin', 'Creó al usuario julito', 'usuario', 38, '2025-11-26 16:45:07'),
(271, 1, 'admin', 'Actualizó al empleado julito alimaña', 'empleado', 35, '2025-11-26 16:45:21'),
(272, 1, 'admin', 'Actualizó el equipo (Código: 2124)', 'equipo', 73, '2025-11-26 17:09:25'),
(273, 1, 'admin', 'Actualizó el equipo (Código: 2124)', 'equipo', 73, '2025-11-26 17:12:54'),
(274, 1, 'admin', 'Actualizó el equipo (Código: 2124)', 'equipo', 73, '2025-11-26 17:12:58'),
(275, 1, 'admin', 'Actualizó el equipo (Código: 2124)', 'equipo', 73, '2025-11-26 17:13:03'),
(276, 1, 'admin', 'Actualizó el equipo (Código: 121)', 'equipo', 72, '2025-11-26 17:13:19'),
(277, 1, 'admin', 'Puso en espera ticket #61', 'soporte', 61, '2025-11-29 14:29:48'),
(278, 1, 'admin', 'Reanudó ticket #61', 'soporte', 61, '2025-11-29 14:29:49'),
(279, 1, 'admin', 'Eliminó el artículo #7 del inventario', 'inventario', 7, '2025-11-29 15:57:52'),
(280, 1, 'admin', 'Eliminó el artículo #6 del inventario', 'inventario', 6, '2025-11-29 16:04:35'),
(281, 1, 'admin', 'Registró nuevo ítem \'Item de Prueba para Borrar\'', 'inventario', NULL, '2025-11-29 16:17:29'),
(282, 1, 'admin', 'Eliminó el artículo #8 del inventario', 'inventario', 8, '2025-11-29 16:58:22'),
(283, 1, 'admin', 'Eliminó el artículo #8 del inventario', 'inventario', 8, '2025-11-29 16:58:22'),
(284, 1, 'admin', 'Eliminó el artículo #5 del inventario', 'inventario', 5, '2025-11-29 16:58:30'),
(285, 1, 'admin', 'Registró nuevo ítem \'Item de Prueba para Borrar\'', 'inventario', NULL, '2025-11-29 17:04:49'),
(286, 1, 'admin', 'Eliminó el artículo #9 del inventario', 'inventario', 9, '2025-11-29 17:05:10'),
(287, 1, 'admin', 'Eliminó el equipo (Código: 2142412)', 'equipo', 71, '2025-11-29 17:36:22'),
(288, 1, 'admin', 'Eliminó el equipo (Código: 123)', 'equipo', 70, '2025-11-29 23:23:40'),
(289, 1, 'admin', 'Eliminó el equipo (Código: 421412)', 'equipo', 67, '2025-11-29 23:23:42'),
(290, 1, 'admin', 'Eliminó el equipo (Código: 41421)', 'equipo', 66, '2025-11-29 23:23:44'),
(291, 1, 'admin', 'Registró nuevo ítem \'test \'', 'inventario', NULL, '2025-11-29 23:24:20'),
(292, 1, 'admin', 'Registró nuevo ítem \'Raton\'', 'inventario', NULL, '2025-11-29 23:24:54'),
(293, 1, 'admin', 'Eliminó el artículo #12 del inventario', 'inventario', 12, '2025-11-29 23:28:32'),
(294, 1, 'admin', 'Eliminó el artículo #10 del inventario', 'inventario', 10, '2025-11-29 23:28:38'),
(295, 1, 'admin', 'Eliminó el equipo (Código: 42142241)', 'equipo', 65, '2025-11-29 23:28:44'),
(296, 1, 'admin', 'Eliminó el equipo (Código: 06)', 'equipo', 64, '2025-11-29 23:28:47'),
(297, 1, 'admin', 'Eliminó el equipo (Código: 05)', 'equipo', 61, '2025-11-29 23:28:49'),
(298, 1, 'admin', 'Eliminó el equipo (Código: 04)', 'equipo', 60, '2025-11-29 23:28:53'),
(299, 1, 'admin', 'Eliminó el equipo (Código: 03)', 'equipo', 59, '2025-11-29 23:28:56'),
(300, 1, 'admin', 'Eliminó ticket #52', 'soporte', 52, '2025-12-01 14:42:33'),
(301, 1, 'admin', 'Resolvió ticket #61', 'soporte', 61, '2025-12-03 14:26:05'),
(302, 1, 'admin', 'Creó ticket #62', 'soporte', 62, '2025-12-03 14:45:03'),
(303, 1, 'admin', 'Guardó observaciones en Ticket #62', 'soporte', 62, '2025-12-03 15:03:03'),
(304, 1, 'admin', 'Asignó ticket #62 a empleado #31', 'soporte', 62, '2025-12-03 15:03:19'),
(305, 1, 'admin', 'Resolvió ticket #62', 'soporte', 62, '2025-12-03 15:03:42'),
(306, 1, 'admin', 'Guardó observaciones en Ticket #62', 'soporte', 62, '2025-12-03 15:28:06'),
(307, 1, 'admin', 'Actualizó fecha de cierre del Ticket #62 a 2026-11-11 11:24:00', 'soporte', 62, '2025-12-03 15:51:40'),
(308, 1, 'admin', 'Actualizó fecha de cierre del Ticket #62 a 2026-11-11 13:24:00', 'soporte', 62, '2025-12-03 15:51:55'),
(309, 1, 'admin', 'Actualizó fecha de cierre del Ticket #62 a 2026-11-11 13:24:00', 'soporte', 62, '2025-12-03 15:51:55'),
(310, 1, 'admin', 'Creó ticket #63', 'soporte', 63, '2025-12-03 15:52:09'),
(311, 1, 'admin', 'Asignó ticket #63 a empleado #31', 'soporte', 63, '2025-12-03 15:52:19'),
(312, 1, 'admin', 'Resolvió ticket #63', 'soporte', 63, '2025-12-03 15:52:41'),
(313, 1, 'admin', 'Asignó ticket #55 a empleado #31', 'soporte', 55, '2025-12-03 15:58:02'),
(314, 1, 'admin', 'Resolvió ticket #55', 'soporte', 55, '2025-12-03 15:58:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos`
--

CREATE TABLE `departamentos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `ubicacion` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `departamentos`
--

INSERT INTO `departamentos` (`id`, `nombre`, `ubicacion`, `created_at`, `updated_at`) VALUES
(21, 'Departamento de Informática', 'Alcaldía Bolivariana de Yaritagua Municipio peña', '2025-11-24 01:40:57', '2025-11-24 01:40:57'),
(23, 'Departamento Catastro', 'Yaritagua, San Jose, Sector Primero de Mayo', '2025-11-24 01:42:30', '2025-11-24 01:42:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `cedula` varchar(15) DEFAULT NULL,
  `departamento_id` int(11) DEFAULT NULL,
  `rol` enum('tecnico','administrador','consultor') NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `nombre`, `apellido`, `email`, `cedula`, `departamento_id`, `rol`, `usuario_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(31, 'Jonas', 'Mendoza', 'jonasmendoza672@gmail.com', '32-076-356', 21, 'tecnico', 35, '2025-11-24 01:39:34', '2025-11-25 02:39:01', NULL),
(32, 'Jose', 'Escalona', 'joseescalona@gmail.com', '31-876-908', 21, 'tecnico', 36, '2025-11-24 01:40:08', '2025-11-24 02:36:26', NULL),
(34, 'Juan', 'Dominguez', 'juandominguez@gmail.com', '14-657-981', 21, 'tecnico', 37, '2025-11-26 14:11:26', '2025-11-26 14:11:45', NULL),
(35, 'julito', 'alimaña', '12412232r312@gmail.com', '123456567', 23, 'tecnico', 38, '2025-11-26 16:43:58', '2025-11-26 16:45:20', NULL),
(37, 'JOSE', 'MESA', 'jonasmendoza672@gmail.com', '24245642', 21, 'tecnico', NULL, '2025-11-29 23:59:58', '2025-11-30 00:01:15', NULL),
(38, 'JOSE', 'MESA', 'jonasmendoza672@gmail.com', '1242412421', 21, 'tecnico', NULL, '2025-11-30 00:01:03', '2025-11-30 00:01:21', NULL),
(39, 'JOSE', 'MESA', 'jonasmendoza672@gmail.com', '1242412412', 21, 'tecnico', NULL, '2025-11-30 00:01:33', '2025-11-30 00:01:33', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos`
--

CREATE TABLE `equipos` (
  `id` int(11) NOT NULL,
  `codigo_inventario` varchar(50) NOT NULL,
  `numero_serie` varchar(100) NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `procesador` varchar(100) DEFAULT NULL,
  `memoria_ram` varchar(50) DEFAULT NULL,
  `almacenamiento` varchar(100) DEFAULT NULL,
  `sistema_operativo` varchar(100) DEFAULT NULL,
  `direccion_ip` varchar(50) DEFAULT NULL,
  `driver` varchar(150) DEFAULT NULL,
  `toner` varchar(100) DEFAULT NULL,
  `departamento_id` int(11) DEFAULT NULL,
  `empleado_id` int(11) DEFAULT NULL COMMENT 'Usuario Asignado',
  `ubicacion_fisica` varchar(255) DEFAULT NULL,
  `estado` enum('nuevo','usado','en_uso','fuera_de_servicio','en_reparacion','disponible','en_reserva') DEFAULT 'nuevo',
  `fecha_compra` date DEFAULT NULL,
  `proveedor` varchar(150) DEFAULT NULL,
  `proveedor_rif` varchar(20) DEFAULT NULL,
  `garantia` date DEFAULT NULL,
  `valor_compra` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `equipos`
--

INSERT INTO `equipos` (`id`, `codigo_inventario`, `numero_serie`, `tipo`, `marca`, `modelo`, `procesador`, `memoria_ram`, `almacenamiento`, `sistema_operativo`, `direccion_ip`, `driver`, `toner`, `departamento_id`, `empleado_id`, `ubicacion_fisica`, `estado`, `fecha_compra`, `proveedor`, `proveedor_rif`, `garantia`, `valor_compra`, `created_at`, `updated_at`) VALUES
(54, '01', '11876567', 'computadora', 'Dell', 'Optiplex tipo slim', 'i7-6700k', '', '126gb ssd', 'Linux', '192.168.0.1', 'Cloun, Talent, Realtek, Nvidia', '', 21, 32, 'Direccion Informática, en el 1er piso a la esquina antes de cruzar.', 'en_reparacion', '2025-11-23', 'Computer Tecnology C.A Barquicenter', '41276582', '2026-01-01', 150.00, '2025-11-24 01:48:09', '2025-11-25 13:09:14'),
(55, '02', '12542876', 'impresora', 'Canon', 'Ordex Versión 2.0', '', '', '', '', '', '', 'Canon 051H', 23, 31, 'Dirección Catastro, Primer Piso, Llegando a la Oficina', 'en_reparacion', '2025-11-23', 'Rafael Computer Tecnology', '41257899', '2026-01-01', 250.00, '2025-11-24 01:54:04', '2025-11-25 02:52:13'),
(63, '12422421412414', '41242142142141', 'computadora', 'DELL', 'OPTIPLEX OFICINA SLIM', '', '', '', '', '', '', '', 21, NULL, '', 'en_uso', NULL, '', '', NULL, NULL, '2025-11-24 23:38:32', '2025-11-24 23:39:52'),
(68, '142121', '42421', 'computadora', 'DELL', 'OPTIPLEX OFICINA SLIM', '', '', '', '', '', '', '', 23, 32, '', 'en_uso', NULL, '', '', NULL, NULL, '2025-11-25 02:00:13', '2025-11-25 02:10:15'),
(72, '121', '12421412412', 'Laptop', 'DELL', 'OPTIPLEX OFICINA SLIM', '', '', '', '', '', '', '', 21, 31, '', 'en_uso', NULL, '', '', NULL, NULL, '2025-11-26 01:30:35', '2025-11-26 17:13:19'),
(73, '2124', '41242112112', 'Laptico', 'DELL', 'OPTIPLEX OFICINA SLIM', '', '', '', '', '', '', '', 21, NULL, '', 'en_uso', NULL, '', '', NULL, NULL, '2025-11-26 01:31:19', '2025-11-26 17:13:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_consumos`
--

CREATE TABLE `inventario_consumos` (
  `id` int(11) NOT NULL,
  `soporte_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_items`
--

CREATE TABLE `inventario_items` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `unidad_medida` varchar(50) DEFAULT NULL,
  `stock_actual` int(11) DEFAULT 0,
  `stock_minimo` int(11) DEFAULT 5,
  `ubicacion` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fecha_compra` date DEFAULT NULL,
  `proveedor` varchar(255) DEFAULT NULL,
  `proveedor_rif` varchar(50) DEFAULT NULL,
  `garantia_fin` date DEFAULT NULL,
  `valor_compra` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_movimientos`
--

CREATE TABLE `inventario_movimientos` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `origen_departamento_id` int(11) DEFAULT NULL,
  `destino_departamento_id` int(11) DEFAULT NULL,
  `tipo_movimiento` enum('ENTRADA','SALIDA','AJUSTE','BAJA','CONSUMO','TRANSFERENCIA') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `referencia_id` int(11) DEFAULT NULL COMMENT 'ID de Ticket o Mantenimiento',
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_ubicaciones`
--

CREATE TABLE `inventario_ubicaciones` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `departamento_id` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mantenimientos`
--

CREATE TABLE `mantenimientos` (
  `id` int(11) NOT NULL,
  `equipo_id` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `proxima_fecha` date DEFAULT NULL,
  `frecuencia` enum('unica','mensual','trimestral','semestral','anual') DEFAULT 'unica',
  `descripcion` text NOT NULL,
  `tipo_mantenimiento` enum('preventivo','correctivo','predictivo') NOT NULL DEFAULT 'preventivo',
  `estado` enum('pendiente','en_proceso','completado','pospuesto','cancelado') NOT NULL DEFAULT 'pendiente',
  `costo` decimal(10,2) DEFAULT 0.00,
  `realizado_por` varchar(150) DEFAULT NULL,
  `tecnico_id` int(11) DEFAULT NULL,
  `checklist` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`checklist`)),
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL COMMENT 'A quién se le muestra la notificación',
  `mensaje` varchar(255) NOT NULL,
  `enlace` varchar(255) DEFAULT NULL COMMENT 'URL a la que lleva (ej: /soportes/ver/10)',
  `leido` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = no leído, 1 = leído',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `usuario_id`, `mensaje`, `enlace`, `leido`, `created_at`) VALUES
(26, 35, 'Nuevo ticket #51 creado.', '/soportes/ver/51', 1, '2025-11-24 02:53:54'),
(27, 1, 'Jose creó un nuevo ticket #51', '/soportes/ver/51', 1, '2025-11-24 02:53:54'),
(28, 35, 'Nuevo ticket #52 creado.', '/soportes/ver/52', 1, '2025-11-25 02:52:13'),
(29, 35, 'Te han asignado el ticket #51', '/soportes/ver/51', 1, '2025-11-25 04:20:02'),
(30, 35, 'Nuevo ticket #53 creado.', '/soportes/ver/53', 1, '2025-11-25 13:09:14'),
(31, 1, 'Jose creó un nuevo ticket #53', '/soportes/ver/53', 1, '2025-11-25 13:09:14'),
(32, 35, 'Nuevo ticket #54 creado.', '/soportes/ver/54', 1, '2025-11-25 13:17:24'),
(33, 1, 'Jose creó un nuevo ticket #54', '/soportes/ver/54', 1, '2025-11-25 13:17:24'),
(34, 35, 'Nuevo ticket #55 creado.', '/soportes/ver/55', 1, '2025-11-25 13:25:06'),
(35, 1, 'Jose creó un nuevo ticket #55', '/soportes/ver/55', 1, '2025-11-25 13:25:06'),
(36, 35, 'Te han asignado el ticket #52', '/soportes/ver/52', 0, '2025-11-25 13:35:09'),
(37, 35, 'Te han asignado el ticket #52', '/soportes/ver/52', 0, '2025-11-25 13:35:09'),
(38, 1, 'Tu ticket #52 ha sido resuelto.', '/soportes/ver/52', 1, '2025-11-25 13:35:23'),
(39, 1, 'Jonas resolvió el ticket #52', '/soportes/ver/52', 1, '2025-11-25 13:35:23'),
(40, 35, 'Nuevo ticket #56 creado.', '/soportes/ver/56', 0, '2025-11-25 23:53:54'),
(41, 1, 'Jose creó un nuevo ticket #56', '/soportes/ver/56', 1, '2025-11-25 23:53:54'),
(42, 35, 'Nuevo ticket #57 creado.', '/soportes/ver/57', 0, '2025-11-26 01:12:21'),
(43, 1, 'Jose creó un nuevo ticket #57', '/soportes/ver/57', 1, '2025-11-26 01:12:21'),
(44, 35, 'Nuevo ticket #58 creado.', '/soportes/ver/58', 0, '2025-11-26 01:13:16'),
(45, 1, 'Jonas creó un nuevo ticket #58', '/soportes/ver/58', 1, '2025-11-26 01:13:16'),
(46, 35, 'Nuevo ticket #59 creado.', '/soportes/ver/59', 0, '2025-11-26 01:18:42'),
(47, 1, 'Jose creó un nuevo ticket #59', '/soportes/ver/59', 1, '2025-11-26 01:18:42'),
(48, 35, 'Nuevo ticket #60 creado.', '/soportes/ver/60', 0, '2025-11-26 01:18:53'),
(49, 1, 'Jose creó un nuevo ticket #60', '/soportes/ver/60', 1, '2025-11-26 01:18:53'),
(50, 35, 'Nuevo ticket #61 creado.', '/soportes/ver/61', 0, '2025-11-26 01:32:33'),
(51, 1, 'Jose creó un nuevo ticket #61', '/soportes/ver/61', 1, '2025-11-26 01:32:33'),
(52, 35, 'Te han asignado el ticket #61', '/soportes/ver/61', 0, '2025-11-26 02:15:57'),
(53, 37, 'Te han asignado el ticket #59', '/soportes/ver/59', 0, '2025-11-26 14:12:11'),
(54, 37, 'Te han asignado el ticket #59', '/soportes/ver/59', 0, '2025-11-26 14:12:11'),
(55, 36, 'Tu ticket #61 ha sido resuelto.', '/soportes/ver/61', 0, '2025-12-03 14:26:05'),
(56, 35, 'Nuevo ticket #62 creado.', '/soportes/ver/62', 0, '2025-12-03 14:45:03'),
(57, 37, 'Nuevo ticket #62 creado.', '/soportes/ver/62', 0, '2025-12-03 14:45:03'),
(58, 38, 'Nuevo ticket #62 creado.', '/soportes/ver/62', 0, '2025-12-03 14:45:03'),
(59, 35, 'Te han asignado el ticket #62', '/soportes/ver/62', 0, '2025-12-03 15:03:19'),
(60, 1, 'Tu ticket #62 ha sido resuelto.', '/soportes/ver/62', 1, '2025-12-03 15:03:42'),
(61, 35, 'Nuevo ticket #63 creado.', '/soportes/ver/63', 0, '2025-12-03 15:52:09'),
(62, 37, 'Nuevo ticket #63 creado.', '/soportes/ver/63', 0, '2025-12-03 15:52:09'),
(63, 38, 'Nuevo ticket #63 creado.', '/soportes/ver/63', 0, '2025-12-03 15:52:09'),
(64, 35, 'Te han asignado el ticket #63', '/soportes/ver/63', 0, '2025-12-03 15:52:19'),
(65, 1, 'Tu ticket #63 ha sido resuelto.', '/soportes/ver/63', 0, '2025-12-03 15:52:41'),
(66, 35, 'Te han asignado el ticket #55', '/soportes/ver/55', 0, '2025-12-03 15:58:02'),
(67, 36, 'Tu ticket #55 ha sido resuelto.', '/soportes/ver/55', 0, '2025-12-03 15:58:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_log`
--

CREATE TABLE `sesiones_log` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sesiones_log`
--

INSERT INTO `sesiones_log` (`id`, `usuario_id`, `username`, `fecha_inicio`, `fecha_fin`, `created_at`) VALUES
(152, 1, 'admin', '2025-11-23 20:39:46', '2025-11-23 20:52:27', '2025-11-24 00:39:46'),
(153, 1, 'admin', '2025-11-23 21:38:06', '2025-11-23 22:03:48', '2025-11-24 01:38:06'),
(154, 35, 'Jonas', '2025-11-23 22:03:56', '2025-11-23 22:18:31', '2025-11-24 02:03:56'),
(155, 35, 'Jonas', '2025-11-23 22:18:36', '2025-11-23 22:24:47', '2025-11-24 02:18:36'),
(156, 35, 'Jonas', '2025-11-23 22:24:57', '2025-11-23 22:29:03', '2025-11-24 02:24:57'),
(157, 35, 'Jonas', '2025-11-23 22:29:08', '2025-11-23 22:30:56', '2025-11-24 02:29:08'),
(158, 36, 'Jose', '2025-11-23 22:31:00', '2025-11-23 22:36:03', '2025-11-24 02:31:00'),
(159, 1, 'admin', '2025-11-23 22:36:08', '2025-11-23 22:36:48', '2025-11-24 02:36:08'),
(160, 36, 'Jose', '2025-11-23 22:36:52', '2025-11-23 22:38:06', '2025-11-24 02:36:52'),
(161, 1, 'admin', '2025-11-23 22:38:11', '2025-11-23 22:39:18', '2025-11-24 02:38:11'),
(162, 36, 'Jose', '2025-11-23 22:39:22', '2025-11-23 22:41:03', '2025-11-24 02:39:22'),
(163, 36, 'Jose', '2025-11-23 22:41:08', '2025-11-23 22:46:01', '2025-11-24 02:41:08'),
(164, 1, 'admin', '2025-11-23 22:46:08', '2025-11-23 22:52:51', '2025-11-24 02:46:08'),
(165, 36, 'Jose', '2025-11-23 22:52:56', '2025-11-23 22:55:53', '2025-11-24 02:52:56'),
(166, 35, 'Jonas', '2025-11-23 22:56:01', '2025-11-23 23:17:15', '2025-11-24 02:56:01'),
(167, 36, 'Jose', '2025-11-24 06:51:51', '2025-11-24 06:52:26', '2025-11-24 10:51:51'),
(168, 35, 'Jonas', '2025-11-24 06:52:32', '2025-11-24 07:52:50', '2025-11-24 10:52:32'),
(169, 1, 'admin', '2025-11-24 07:55:02', '2025-11-24 08:06:15', '2025-11-24 11:55:02'),
(170, 1, 'admin', '2025-11-24 09:02:22', '2025-11-24 09:38:12', '2025-11-24 13:02:22'),
(171, 1, 'admin', '2025-11-24 09:42:35', '2025-11-24 09:43:54', '2025-11-24 13:42:35'),
(172, 1, 'admin', '2025-11-24 09:44:54', '2025-11-24 09:45:00', '2025-11-24 13:44:54'),
(173, 36, 'Jose', '2025-11-24 10:59:13', '2025-11-24 10:59:21', '2025-11-24 14:59:13'),
(174, 1, 'admin', '2025-11-24 10:59:26', '2025-11-24 11:14:37', '2025-11-24 14:59:26'),
(175, 1, 'admin', '2025-11-24 11:25:39', '2025-11-24 16:12:10', '2025-11-24 15:25:39'),
(176, 36, 'Jose', '2025-11-24 16:11:26', '2025-11-24 16:12:05', '2025-11-24 20:11:26'),
(177, 1, 'admin', '2025-11-24 16:12:10', '2025-11-24 16:24:14', '2025-11-24 20:12:10'),
(178, 1, 'admin', '2025-11-24 16:51:49', '2025-11-24 17:45:20', '2025-11-24 20:51:49'),
(179, 1, 'admin', '2025-11-24 18:56:13', '2025-11-24 19:24:37', '2025-11-24 22:56:13'),
(180, 1, 'admin', '2025-11-24 19:29:31', '2025-11-24 20:36:18', '2025-11-24 23:29:31'),
(181, 1, 'admin', '2025-11-24 21:02:48', '2025-11-25 00:21:44', '2025-11-25 01:02:48'),
(182, 1, 'admin', '2025-11-25 01:10:47', '2025-11-25 01:11:26', '2025-11-25 05:10:47'),
(183, 1, 'admin', '2025-11-25 09:03:49', '2025-11-25 09:04:10', '2025-11-25 13:03:49'),
(184, 36, 'Jose', '2025-11-25 09:04:17', '2025-11-25 09:05:03', '2025-11-25 13:04:17'),
(185, 1, 'admin', '2025-11-25 09:05:09', '2025-11-25 09:06:19', '2025-11-25 13:05:09'),
(186, 36, 'Jose', '2025-11-25 09:06:26', '2025-11-25 09:26:10', '2025-11-25 13:06:26'),
(187, 35, 'Jonas', '2025-11-25 09:26:14', '2025-11-25 09:36:21', '2025-11-25 13:26:14'),
(188, 1, 'admin', '2025-11-25 09:36:27', '2025-11-25 09:48:21', '2025-11-25 13:36:27'),
(189, 35, 'Jonas', '2025-11-25 09:48:25', '2025-11-25 09:49:27', '2025-11-25 13:48:25'),
(190, 36, 'Jose', '2025-11-25 19:53:29', '2025-11-25 19:54:24', '2025-11-25 23:53:29'),
(191, 1, 'admin', '2025-11-25 19:54:31', '2025-11-25 20:13:14', '2025-11-25 23:54:31'),
(192, 36, 'Jose', '2025-11-25 21:07:31', '2025-11-25 21:12:26', '2025-11-26 01:07:31'),
(193, 35, 'Jonas', '2025-11-25 21:12:30', '2025-11-25 21:15:17', '2025-11-26 01:12:30'),
(194, 36, 'Jose', '2025-11-25 21:15:24', '2025-11-25 21:15:40', '2025-11-26 01:15:24'),
(195, 35, 'Jonas', '2025-11-25 21:15:47', '2025-11-25 21:18:05', '2025-11-26 01:15:47'),
(196, 36, 'Jose', '2025-11-25 21:18:11', '2025-11-25 21:19:28', '2025-11-26 01:18:11'),
(197, 1, 'admin', '2025-11-25 21:19:32', '2025-11-25 21:31:41', '2025-11-26 01:19:32'),
(198, 35, 'Jonas', '2025-11-25 21:31:45', '2025-11-25 21:32:03', '2025-11-26 01:31:45'),
(199, 36, 'Jose', '2025-11-25 21:32:08', '2025-11-25 21:32:42', '2025-11-26 01:32:08'),
(200, 1, 'admin', '2025-11-25 22:15:27', '2025-11-26 09:58:18', '2025-11-26 02:15:27'),
(201, 1, 'admin', '2025-11-26 10:06:35', '2025-11-26 10:11:51', '2025-11-26 14:06:35'),
(202, 37, 'Juan', '2025-11-26 10:11:59', '2025-11-26 10:11:59', '2025-11-26 14:11:59'),
(203, 37, 'Juan', '2025-11-26 10:11:59', '2025-11-26 10:26:41', '2025-11-26 14:11:59'),
(204, 1, 'admin', '2025-11-26 12:27:04', '2025-11-26 12:28:42', '2025-11-26 16:27:04'),
(205, 37, 'Juan', '2025-11-26 12:28:53', '2025-11-26 12:29:06', '2025-11-26 16:28:53'),
(206, 35, 'Jonas', '2025-11-26 12:29:14', '2025-11-26 12:29:23', '2025-11-26 16:29:14'),
(207, 1, 'admin', '2025-11-26 12:29:31', '2025-11-26 12:35:44', '2025-11-26 16:29:31'),
(208, 37, 'Juan', '2025-11-26 12:35:53', '2025-11-26 12:38:26', '2025-11-26 16:35:53'),
(209, 36, 'Jose', '2025-11-26 12:38:34', '2025-11-26 12:38:57', '2025-11-26 16:38:34'),
(210, 35, 'Jonas', '2025-11-26 12:39:02', '2025-11-26 12:39:12', '2025-11-26 16:39:02'),
(211, 37, 'Juan', '2025-11-26 12:39:17', '2025-11-26 12:42:42', '2025-11-26 16:39:17'),
(212, 1, 'admin', '2025-11-26 12:42:48', '2025-11-26 12:43:04', '2025-11-26 16:42:48'),
(213, 37, 'Juan', '2025-11-26 12:43:09', '2025-11-26 12:43:17', '2025-11-26 16:43:09'),
(214, 1, 'admin', '2025-11-26 12:43:23', '2025-11-26 12:45:25', '2025-11-26 16:43:23'),
(215, 38, 'julito', '2025-11-26 12:45:30', '2025-11-26 12:50:08', '2025-11-26 16:45:30'),
(216, 1, 'admin', '2025-11-26 12:50:13', '2025-11-26 12:53:57', '2025-11-26 16:50:13'),
(217, 1, 'admin', '2025-11-26 12:54:02', '2025-11-26 13:14:51', '2025-11-26 16:54:02'),
(218, 1, 'admin', '2025-11-29 10:29:02', '2025-11-29 10:43:17', '2025-11-29 14:29:02'),
(219, 1, 'admin', '2025-11-29 10:37:35', '2025-11-29 10:49:13', '2025-11-29 14:37:35'),
(220, 1, 'admin', '2025-11-29 10:49:13', '2025-11-29 11:10:14', '2025-11-29 14:49:13'),
(221, 1, 'admin', '2025-11-29 11:02:39', '2025-11-29 11:15:30', '2025-11-29 15:02:39'),
(222, 1, 'admin', '2025-11-29 11:18:19', '2025-11-29 11:45:17', '2025-11-29 15:18:19'),
(223, 1, 'admin', '2025-11-29 11:47:15', '2025-11-29 12:20:50', '2025-11-29 15:47:15'),
(224, 1, 'admin', '2025-11-29 12:23:49', '2025-11-29 12:26:12', '2025-11-29 16:23:49'),
(225, 1, 'admin', '2025-11-29 12:58:04', '2025-11-29 13:10:38', '2025-11-29 16:58:04'),
(226, 1, 'admin', '2025-11-29 13:14:56', '2025-11-29 13:26:14', '2025-11-29 17:14:56'),
(227, 1, 'admin', '2025-11-29 13:30:07', '2025-11-29 13:38:51', '2025-11-29 17:30:07'),
(228, 1, 'admin', '2025-11-29 13:40:24', '2025-11-29 13:40:27', '2025-11-29 17:40:24'),
(229, 1, 'admin', '2025-11-29 13:40:43', '2025-11-29 13:50:59', '2025-11-29 17:40:43'),
(230, 1, 'admin', '2025-11-29 14:13:56', '2025-11-29 14:24:57', '2025-11-29 18:13:56'),
(231, 1, 'admin', '2025-11-29 14:33:00', '2025-11-29 14:43:59', '2025-11-29 18:33:00'),
(232, 1, 'admin', '2025-11-29 17:44:21', '2025-11-29 17:55:03', '2025-11-29 21:44:21'),
(233, 1, 'admin', '2025-11-29 18:09:03', '2025-11-29 18:20:26', '2025-11-29 22:09:03'),
(234, 1, 'admin', '2025-11-29 19:20:11', '2025-11-29 19:20:36', '2025-11-29 23:20:11'),
(235, 1, 'admin', '2025-11-29 19:20:36', '2025-11-29 19:25:33', '2025-11-29 23:20:36'),
(236, 1, 'admin', '2025-11-29 19:27:13', '2025-11-29 19:50:10', '2025-11-29 23:27:13'),
(237, 1, 'admin', '2025-11-29 19:40:57', '2025-11-29 20:11:43', '2025-11-29 23:40:57'),
(238, 1, 'admin', '2025-12-01 10:42:23', '2025-12-01 10:52:54', '2025-12-01 14:42:23'),
(239, 1, 'admin', '2025-12-03 10:25:56', '2025-12-03 10:37:46', '2025-12-03 14:25:56'),
(240, 1, 'admin', '2025-12-03 10:44:17', '2025-12-03 11:46:11', '2025-12-03 14:44:17'),
(241, 1, 'admin', '2025-12-03 11:48:56', NULL, '2025-12-03 15:48:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `soportes`
--

CREATE TABLE `soportes` (
  `id` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `descripcion` text NOT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` enum('pendiente','en_proceso','en_espera','resuelto') NOT NULL DEFAULT 'pendiente',
  `equipo_id` int(11) NOT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `usuario_creacion_id` int(11) DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `soportes`
--

INSERT INTO `soportes` (`id`, `fecha`, `descripcion`, `observaciones`, `estado`, `equipo_id`, `empleado_id`, `usuario_creacion_id`, `fecha_cierre`, `created_at`, `updated_at`) VALUES
(53, '2025-11-25 09:09:14', 'prueba de automatización 1', NULL, 'pendiente', 54, NULL, 36, NULL, '2025-11-25 13:09:14', '2025-11-25 13:24:47'),
(55, '2025-11-25 09:25:06', 'ddwdwdawdwaddaawwaawd', NULL, 'resuelto', 54, 31, 36, '2025-12-03 11:58:04', '2025-11-25 13:25:06', '2025-12-03 15:58:04'),
(59, '2025-11-25 21:18:42', 'awdaadwdaddawdadas', NULL, 'en_proceso', 54, 34, 36, NULL, '2025-11-26 01:18:42', '2025-11-26 14:12:11'),
(61, '2025-11-25 21:32:33', 'que ees soeoapdmpaopdaopadpaw', NULL, 'resuelto', 73, 31, 36, NULL, '2025-11-26 01:32:33', '2025-12-03 14:26:05'),
(62, '2025-12-03 10:45:03', 'dlsmdioasmdisaomdiasdoimsaoisdaas', 'Se verifico la fuente de poder para que despues se haga un cambio bien hecho', 'resuelto', 55, 31, 1, '2026-11-11 13:24:00', '2025-12-03 14:45:03', '2025-12-03 15:51:55'),
(63, '2025-12-03 11:52:09', 'sdffqfqwfdqwqwd', NULL, 'resuelto', 54, 31, 1, '2025-12-03 16:52:41', '2025-12-03 15:52:09', '2025-12-03 15:52:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','tecnico','consultor') NOT NULL DEFAULT 'consultor',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tema` varchar(10) DEFAULT 'light',
  `empleado_id` int(11) DEFAULT NULL,
  `departamento_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `rol`, `created_at`, `updated_at`, `tema`, `empleado_id`, `departamento_id`) VALUES
(1, 'admin', '$2y$10$j/rQELVVIUCB1OZZCf9yquQmcDIgRj.wz5cSBcLRwTHWFd/fwAQuG', 'admin', '2025-10-27 18:45:22', '2025-11-23 21:35:55', 'light', NULL, NULL),
(35, 'Jonas', '$2y$10$3jRZJNWSuFpxZfRRA3Ul9.b.1.Zj/Jcu2Y/Js3rswH6brYsxjZazC', 'tecnico', '2025-11-24 01:38:49', '2025-11-24 02:28:56', 'light', 31, NULL),
(36, 'Jose', '$2y$10$f7aQ5KPFw1Jz4vdAT8bzOe84.tsRH1eGzuQJzrFRsfJQNaZrK6kKq', 'consultor', '2025-11-24 01:38:59', '2025-11-24 02:40:54', 'light', 32, NULL),
(37, 'Juan', '$2y$10$7AalZlBSQTjzRS286kQ1TOdTCIKcCV97pdoA5cb4zJNJpX9vYTMs.', 'tecnico', '2025-11-26 14:11:38', '2025-11-26 14:11:38', 'light', NULL, NULL),
(38, 'julito', '$2y$10$a.kCsL8hHCPUxn1yL04gk.uutClCJX2TK.qYP/hkWn45QrqWwSkGO', 'tecnico', '2025-11-26 16:45:07', '2025-11-26 16:45:07', 'light', 35, 23);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bajas_inventario`
--
ALTER TABLE `bajas_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventario_id` (`inventario_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `bitacora_acciones`
--
ALTER TABLE `bitacora_acciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `departamento_id` (`departamento_id`);

--
-- Indices de la tabla `equipos`
--
ALTER TABLE `equipos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial` (`numero_serie`),
  ADD KEY `fk_equipos_departamento` (`departamento_id`);

--
-- Indices de la tabla `inventario_consumos`
--
ALTER TABLE `inventario_consumos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `soporte_id` (`soporte_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `inventario_items`
--
ALTER TABLE `inventario_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `inventario_movimientos`
--
ALTER TABLE `inventario_movimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `fk_mov_origen` (`origen_departamento_id`),
  ADD KEY `fk_mov_destino` (`destino_departamento_id`);

--
-- Indices de la tabla `inventario_ubicaciones`
--
ALTER TABLE `inventario_ubicaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_stock` (`item_id`,`departamento_id`),
  ADD KEY `departamento_id` (`departamento_id`);

--
-- Indices de la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipo_id` (`equipo_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_proxima_fecha` (`proxima_fecha`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `sesiones_log`
--
ALTER TABLE `sesiones_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `soportes`
--
ALTER TABLE `soportes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipo_id` (`equipo_id`),
  ADD KEY `empleado_id` (`empleado_id`),
  ADD KEY `estado` (`estado`),
  ADD KEY `fecha` (`fecha`),
  ADD KEY `fk_soportes_usuario_creacion` (`usuario_creacion_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_usuario_empleado` (`empleado_id`),
  ADD KEY `fk_usuario_departamento` (`departamento_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bajas_inventario`
--
ALTER TABLE `bajas_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `bitacora_acciones`
--
ALTER TABLE `bitacora_acciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=315;

--
-- AUTO_INCREMENT de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `equipos`
--
ALTER TABLE `equipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT de la tabla `inventario_consumos`
--
ALTER TABLE `inventario_consumos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `inventario_items`
--
ALTER TABLE `inventario_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `inventario_movimientos`
--
ALTER TABLE `inventario_movimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `inventario_ubicaciones`
--
ALTER TABLE `inventario_ubicaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT de la tabla `sesiones_log`
--
ALTER TABLE `sesiones_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=242;

--
-- AUTO_INCREMENT de la tabla `soportes`
--
ALTER TABLE `soportes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bajas_inventario`
--
ALTER TABLE `bajas_inventario`
  ADD CONSTRAINT `bajas_inventario_ibfk_1` FOREIGN KEY (`inventario_id`) REFERENCES `inventario_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bajas_inventario_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `bitacora_acciones`
--
ALTER TABLE `bitacora_acciones`
  ADD CONSTRAINT `bitacora_acciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD CONSTRAINT `empleados_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `empleados_ibfk_2` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `equipos`
--
ALTER TABLE `equipos`
  ADD CONSTRAINT `equipos_ibfk_1` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_equipos_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `inventario_consumos`
--
ALTER TABLE `inventario_consumos`
  ADD CONSTRAINT `inventario_consumos_ibfk_1` FOREIGN KEY (`soporte_id`) REFERENCES `soportes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventario_consumos_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventario_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventario_consumos_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `inventario_movimientos`
--
ALTER TABLE `inventario_movimientos`
  ADD CONSTRAINT `fk_mov_destino` FOREIGN KEY (`destino_departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mov_origen` FOREIGN KEY (`origen_departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventario_movimientos_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventario_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventario_movimientos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `inventario_ubicaciones`
--
ALTER TABLE `inventario_ubicaciones`
  ADD CONSTRAINT `inventario_ubicaciones_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventario_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventario_ubicaciones_ibfk_2` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  ADD CONSTRAINT `mantenimientos_ibfk_1` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sesiones_log`
--
ALTER TABLE `sesiones_log`
  ADD CONSTRAINT `sesiones_log_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `soportes`
--
ALTER TABLE `soportes`
  ADD CONSTRAINT `fk_soportes_usuario_creacion` FOREIGN KEY (`usuario_creacion_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `soportes_ibfk_1` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `soportes_ibfk_2` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_usuario_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

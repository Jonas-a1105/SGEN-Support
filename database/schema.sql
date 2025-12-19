-- =============================================
-- SGEN-Support - Database Schema
-- Version: 1.0.2
-- Last Updated: 2025-12-10
-- =============================================
-- 
-- Este archivo contiene la estructura completa de la base de datos.
-- Para una instalación nueva, ejecutar este archivo completo.
-- Para actualizaciones, usar los archivos de migración en /migrations/
-- 
-- =============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------
-- Database Creation
-- ---------------------------------------------
CREATE DATABASE IF NOT EXISTS `sgen_db` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `sgen_db`;

-- =============================================
-- TABLA: usuarios
-- Almacena los usuarios del sistema con sus credenciales y roles
-- =============================================
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `rol` ENUM('admin', 'tecnico', 'consultor') NOT NULL DEFAULT 'consultor',
  `tema` VARCHAR(20) DEFAULT 'light',
  `empleado_id` INT NULL COMMENT 'Referencia al empleado vinculado',
  `departamento_id` INT NULL COMMENT 'Departamento del usuario (para filtros)',
  `activo` BOOLEAN DEFAULT TRUE,
  `ultimo_acceso` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_rol` (`rol`),
  INDEX `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: sesiones_log
-- Registro de inicios y cierres de sesión
-- =============================================
CREATE TABLE IF NOT EXISTS `sesiones_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `fecha_inicio` DATETIME NOT NULL,
  `fecha_fin` DATETIME NULL COMMENT 'NULL hasta que el usuario cierre sesión',
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  INDEX `idx_usuario` (`usuario_id`),
  INDEX `idx_fecha_inicio` (`fecha_inicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: departamentos
-- Departamentos/áreas de la organización
-- =============================================
CREATE TABLE IF NOT EXISTS `departamentos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` TEXT NULL,
  `ubicacion` VARCHAR(150) NULL,
  `telefono` VARCHAR(20) NULL,
  `email` VARCHAR(150) NULL,
  `jefe_area_id` INT NULL COMMENT 'Empleado jefe del área',
  `jefe_area_nombre` VARCHAR(150) NULL COMMENT 'Nombre manual del jefe',
  `activo` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_nombre` (`nombre`),
  INDEX `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: empleados
-- Personal de la organización
-- =============================================
CREATE TABLE IF NOT EXISTS `empleados` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NULL,
  `cedula` VARCHAR(15) NULL UNIQUE COMMENT 'Documento de identidad',
  `email` VARCHAR(150) NULL,
  `telefono` VARCHAR(20) NULL,
  `cargo` VARCHAR(100) NULL,
  `departamento_id` INT NULL,
  `usuario_id` INT NULL COMMENT 'Usuario del sistema vinculado',
  `activo` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`departamento_id`) REFERENCES `departamentos`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
  INDEX `idx_departamento` (`departamento_id`),
  INDEX `idx_nombre` (`nombre`),
  INDEX `idx_cedula` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar FK de jefe_area después de crear empleados
ALTER TABLE `departamentos` 
  ADD CONSTRAINT `fk_jefe_area` 
  FOREIGN KEY (`jefe_area_id`) REFERENCES `empleados`(`id`) ON DELETE SET NULL;

-- =============================================
-- TABLA: equipos
-- Inventario de equipos/activos tecnológicos
-- =============================================
CREATE TABLE IF NOT EXISTS `equipos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo_inventario` VARCHAR(50) NULL UNIQUE COMMENT 'Código interno de inventario',
  `numero_serie` VARCHAR(100) NULL COMMENT 'Número de serie del fabricante',
  `tipo` VARCHAR(50) NOT NULL COMMENT 'computadora, laptop, impresora, etc.',
  `marca` VARCHAR(100) NULL,
  `modelo` VARCHAR(100) NULL,
  `estado` VARCHAR(30) NOT NULL DEFAULT 'disponible' COMMENT 'disponible, en_uso, en_reparacion, fuera_de_servicio, nuevo, usado, en_reserva',
  `procesador` VARCHAR(100) NULL,
  `memoria_ram` VARCHAR(50) NULL,
  `almacenamiento` VARCHAR(100) NULL,
  `sistema_operativo` VARCHAR(100) NULL,
  `direccion_ip` VARCHAR(45) NULL,
  `driver` VARCHAR(150) NULL COMMENT 'Para impresoras',
  `toner` VARCHAR(100) NULL COMMENT 'Para impresoras',
  `ubicacion_fisica` VARCHAR(150) NULL,
  `fecha_compra` DATE NULL,
  `garantia` DATE NULL COMMENT 'Fecha fin de garantía',
  `valor_compra` DECIMAL(12,2) NULL,
  `proveedor` VARCHAR(150) NULL,
  `proveedor_rif` VARCHAR(30) NULL COMMENT 'RIF del proveedor',
  `observaciones` TEXT NULL,
  `departamento_id` INT NULL,
  `empleado_id` INT NULL COMMENT 'Empleado asignado',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`departamento_id`) REFERENCES `departamentos`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`empleado_id`) REFERENCES `empleados`(`id`) ON DELETE SET NULL,
  INDEX `idx_codigo` (`codigo_inventario`),
  INDEX `idx_serial` (`numero_serie`),
  INDEX `idx_tipo` (`tipo`),
  INDEX `idx_estado` (`estado`),
  INDEX `idx_departamento` (`departamento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: categorias
-- Categorías de tickets de soporte
-- =============================================
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` TEXT NULL,
  `icono` VARCHAR(50) DEFAULT 'bi-tools' COMMENT 'Clase de icono Bootstrap',
  `color` VARCHAR(20) DEFAULT '#6c757d' COMMENT 'Color hexadecimal',
  `activo` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos iniciales de categorías
INSERT INTO `categorias` (`nombre`, `descripcion`, `icono`, `color`) VALUES
  ('Hardware', 'Fallos de componentes físicos', 'bi-cpu', '#dc3545'),
  ('Software', 'Errores de programas y SO', 'bi-window', '#0d6efd'),
  ('Red', 'Conectividad e internet', 'bi-wifi', '#198754'),
  ('Impresora', 'Problemas de impresión', 'bi-printer', '#ffc107'),
  ('Periféricos', 'Mouse, teclado, monitor', 'bi-mouse', '#6c757d'),
  ('Otro', 'Otros problemas', 'bi-question-circle', '#6c757d')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- =============================================
-- TABLA: soportes
-- Tickets de soporte técnico
-- =============================================
CREATE TABLE IF NOT EXISTS `soportes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fecha` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `titulo` VARCHAR(255) NULL,
  `descripcion` TEXT NOT NULL,
  `estado` ENUM('pendiente', 'asignado', 'en_proceso', 'resuelto', 'cerrado') NOT NULL DEFAULT 'pendiente',
  `prioridad` ENUM('baja', 'media', 'alta', 'critica') DEFAULT 'media',
  `categoria_id` INT NULL,
  `equipo_id` INT NOT NULL,
  `empleado_id` INT NULL COMMENT 'Técnico asignado',
  `usuario_creacion_id` INT NULL COMMENT 'Usuario que creó el ticket',
  `fecha_asignacion` DATETIME NULL,
  `fecha_cierre` DATETIME NULL,
  `fecha_vencimiento` DATETIME NULL COMMENT 'SLA - fecha límite',
  `notificacion_vencimiento_enviada` BOOLEAN DEFAULT FALSE,
  `notificacion_vencimiento_fecha` DATETIME NULL,
  `solucion` TEXT NULL COMMENT 'Descripción de la solución',
  `observaciones` TEXT NULL,
  `firma` LONGTEXT NULL COMMENT 'Firma digital en base64',
  `valoracion` TINYINT NULL COMMENT 'Calificación 1-5',
  `valoracion_comentario` TEXT NULL,
  `valoracion_fecha` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`equipo_id`) REFERENCES `equipos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`empleado_id`) REFERENCES `empleados`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`usuario_creacion_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
  INDEX `idx_estado` (`estado`),
  INDEX `idx_prioridad` (`prioridad`),
  INDEX `idx_fecha` (`fecha`),
  INDEX `idx_equipo` (`equipo_id`),
  INDEX `idx_empleado` (`empleado_id`),
  INDEX `idx_categoria` (`categoria_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: ticket_comentarios
-- Comentarios en tickets de soporte
-- =============================================
CREATE TABLE IF NOT EXISTS `ticket_comentarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `usuario_id` INT NOT NULL,
  `comentario` TEXT NOT NULL,
  `es_interno` BOOLEAN DEFAULT FALSE COMMENT 'Nota interna solo visible para técnicos',
  `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`ticket_id`) REFERENCES `soportes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  INDEX `idx_ticket` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: ticket_archivos
-- Archivos adjuntos a tickets
-- =============================================
CREATE TABLE IF NOT EXISTS `ticket_archivos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `nombre_archivo` VARCHAR(255) NOT NULL COMMENT 'Nombre en el servidor',
  `nombre_original` VARCHAR(255) NOT NULL COMMENT 'Nombre original del archivo',
  `ruta` VARCHAR(500) NOT NULL,
  `tipo_mime` VARCHAR(100) NULL,
  `tamaño_bytes` INT NULL,
  `subido_por` INT NULL,
  `fecha_subida` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`ticket_id`) REFERENCES `soportes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subido_por`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
  INDEX `idx_ticket` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: mantenimientos
-- Registro de mantenimientos de equipos
-- =============================================
CREATE TABLE IF NOT EXISTS `mantenimientos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `equipo_id` INT NOT NULL,
  `tipo_mantenimiento` ENUM('preventivo', 'correctivo', 'predictivo') NOT NULL DEFAULT 'preventivo',
  `estado` ENUM('pendiente', 'en_proceso', 'completado', 'pospuesto', 'cancelado') NOT NULL DEFAULT 'pendiente',
  `descripcion` TEXT NOT NULL,
  `fecha` DATETIME NOT NULL COMMENT 'Fecha programada/realizada',
  `proxima_fecha` DATE NULL COMMENT 'Próximo mantenimiento programado',
  `costo` DECIMAL(10,2) DEFAULT 0.00,
  `realizado_por` VARCHAR(150) NULL,
  `tecnico_id` INT NULL,
  `checklist` JSON NULL COMMENT 'Lista de verificación en formato JSON',
  `observaciones` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`equipo_id`) REFERENCES `equipos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tecnico_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
  INDEX `idx_equipo` (`equipo_id`),
  INDEX `idx_estado` (`estado`),
  INDEX `idx_proxima_fecha` (`proxima_fecha`),
  INDEX `idx_tipo` (`tipo_mantenimiento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: inventario_items
-- Ítems de inventario consumible (suministros)
-- =============================================
CREATE TABLE IF NOT EXISTS `inventario_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo` VARCHAR(50) NULL UNIQUE,
  `nombre` VARCHAR(150) NOT NULL,
  `descripcion` TEXT NULL,
  `categoria` VARCHAR(50) NULL,
  `unidad_medida` VARCHAR(20) DEFAULT 'unidad',
  `stock_minimo` INT DEFAULT 0,
  `stock_actual` INT DEFAULT 0,
  `valor_unitario` DECIMAL(12,2) DEFAULT 0.00,
  `valor_compra` DECIMAL(12,2) DEFAULT 0.00,
  `ubicacion` VARCHAR(100) NULL,
  `proveedor` VARCHAR(150) NULL,
  `activo` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_codigo` (`codigo`),
  INDEX `idx_nombre` (`nombre`),
  INDEX `idx_categoria` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: inventario_ubicaciones
-- Stock por departamento (inventario distribuido)
-- =============================================
CREATE TABLE IF NOT EXISTS `inventario_ubicaciones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `item_id` INT NOT NULL,
  `departamento_id` INT NULL COMMENT 'NULL = Almacén Central',
  `cantidad` INT NOT NULL DEFAULT 0,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`item_id`) REFERENCES `inventario_items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`departamento_id`) REFERENCES `departamentos`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_stock` (`item_id`, `departamento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: inventario_movimientos
-- Historial de movimientos de inventario
-- =============================================
CREATE TABLE IF NOT EXISTS `inventario_movimientos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `item_id` INT NOT NULL,
  `tipo` ENUM('entrada', 'salida', 'ajuste', 'transferencia', 'consumo', 'baja') NOT NULL,
  `cantidad` INT NOT NULL,
  `motivo` TEXT NULL,
  `usuario_id` INT NULL,
  `origen_departamento_id` INT NULL,
  `destino_departamento_id` INT NULL,
  `referencia_tipo` VARCHAR(50) NULL COMMENT 'soporte, compra, etc.',
  `referencia_id` INT NULL,
  `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`item_id`) REFERENCES `inventario_items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`origen_departamento_id`) REFERENCES `departamentos`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`destino_departamento_id`) REFERENCES `departamentos`(`id`) ON DELETE SET NULL,
  INDEX `idx_item` (`item_id`),
  INDEX `idx_tipo` (`tipo`),
  INDEX `idx_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: inventario_consumos
-- Consumos de inventario en tickets de soporte
-- =============================================
CREATE TABLE IF NOT EXISTS `inventario_consumos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `soporte_id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `cantidad` INT NOT NULL,
  `usuario_id` INT NOT NULL,
  `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`soporte_id`) REFERENCES `soportes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`item_id`) REFERENCES `inventario_items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  INDEX `idx_soporte` (`soporte_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: bajas_inventario
-- Registro de bajas de inventario
-- =============================================
CREATE TABLE IF NOT EXISTS `bajas_inventario` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `inventario_id` INT NOT NULL,
  `cantidad` INT NOT NULL,
  `motivo` TEXT NULL,
  `fecha` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` INT NULL,
  FOREIGN KEY (`inventario_id`) REFERENCES `inventario_items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: notificaciones
-- Sistema de notificaciones internas
-- =============================================
CREATE TABLE IF NOT EXISTS `notificaciones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `mensaje` TEXT NOT NULL,
  `tipo` VARCHAR(50) DEFAULT 'info' COMMENT 'info, success, warning, danger',
  `enlace` VARCHAR(500) NULL COMMENT 'URL de destino al hacer clic',
  `leido` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  INDEX `idx_usuario` (`usuario_id`),
  INDEX `idx_leido` (`leido`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: bitacora_acciones
-- Registro de auditoría de acciones del sistema
-- =============================================
CREATE TABLE IF NOT EXISTS `bitacora_acciones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NULL COMMENT 'El usuario que realizó la acción',
  `username` VARCHAR(50) NOT NULL COMMENT 'Nombre del usuario en ese momento',
  `accion` VARCHAR(255) NOT NULL COMMENT 'Descripción de la acción',
  `enlace_tipo` VARCHAR(50) NULL COMMENT 'Tipo de entidad vinculada (ej: equipo)',
  `enlace_id` INT NULL COMMENT 'ID de la entidad vinculada',
  `entidad` VARCHAR(50) NULL COMMENT 'Tabla afectada',
  `datos_anteriores` JSON NULL COMMENT 'Estado anterior del registro',
  `datos_nuevos` JSON NULL COMMENT 'Estado nuevo del registro',
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
  INDEX `idx_usuario` (`usuario_id`),
  INDEX `idx_entidad` (`entidad`, `entidad_id`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- USUARIO ADMINISTRADOR POR DEFECTO
-- Usuario: admin / Contraseña: admin123
-- =============================================
INSERT INTO `usuarios` (`username`, `password`, `rol`) VALUES
  ('admin', '$2y$10$UzcTNIBXYdAEBfdzynSn8uBfjd5Z5SELvQGOSWOcH/o.BaaaL88ja', 'admin')
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- FIN DEL SCHEMA
-- =============================================
<?php
/**
 * Script de sincronización de esquema para SGEN-Support Desktop
 * Asegura que la base de datos importada tenga todas las columnas necesarias para la versión 1.0.18
 */

require_once __DIR__ . '/../config/database.php';

try {
    // 1. Conexión inicial sin base de datos para asegurar que exista
    $dsnNoDB = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
    $pdoInit = new PDO($dsnNoDB, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdoInit->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdoInit = null;

    // 2. Conectar a la base de datos específica
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "✅ Conectado a la base de datos " . DB_NAME . " en el puerto " . DB_PORT . ".\n";

    // --- VERIFICACIÓN DE BOOTSTRAP ---
    $stmt = $pdo->query("SHOW TABLES LIKE 'soportes'");
    if ($stmt->rowCount() === 0) {
        echo "   ⚠️  No se encontró la tabla 'soportes'. Iniciando bootstrap...\n";
        
        // Try multiple locations for migration file
        $migrationPaths = [
            __DIR__ . '/../migrations/m0000_00_00_initial_setup.php',
            dirname(__DIR__) . '/migrations/m0000_00_00_initial_setup.php',
        ];
        
        $resourcesPath = getenv('RESOURCES_PATH') ?: ($_ENV['RESOURCES_PATH'] ?? null);
        if ($resourcesPath) {
            array_unshift($migrationPaths, $resourcesPath . '/migrations/m0000_00_00_initial_setup.php');
        }
        
        $migrationLoaded = false;
        foreach ($migrationPaths as $migrationFile) {
            echo "   🔍 Buscando migración: $migrationFile\n";
            if (file_exists($migrationFile)) {
                $migration = require $migrationFile;
                if (is_array($migration) && isset($migration['up'])) {
                    $migration['up']($pdo);
                    echo "   ✅ Bootstrap completado.\n";
                    $migrationLoaded = true;
                    break;
                }
            }
        }
        
        if (!$migrationLoaded) {
            echo "   ❌ Error: No se encontró el archivo de migración inicial. Creando tablas manualmente...\n";
            // Fallback: crear tablas esenciales directamente
            createEssentialTables($pdo);
        }
    }

    // --- TABLA: departamentos ---
    echo "🔍 Sincronizando tabla 'departamentos'...\n";
    add_column_if_not_exists($pdo, 'departamentos', 'descripcion', 'TEXT NULL AFTER nombre');
    add_column_if_not_exists($pdo, 'departamentos', 'jefe_area_id', 'INT NULL AFTER email');
    add_column_if_not_exists($pdo, 'departamentos', 'jefe_area_nombre', 'VARCHAR(150) NULL AFTER jefe_area_id');
    
    // --- TABLA: soportes ---
    echo "🔍 Sincronizando tabla 'soportes'...\n";
    add_column_if_not_exists($pdo, 'soportes', 'titulo', 'VARCHAR(255) NULL AFTER fecha');
    
    // Corregir ENUM de prioridad
    echo "   🔄 Actualizando ENUM de prioridad en 'soportes'...\n";
    $pdo->exec("ALTER TABLE soportes MODIFY COLUMN prioridad ENUM('baja', 'media', 'alta', 'critica') DEFAULT 'media'");

    add_column_if_not_exists($pdo, 'soportes', 'categoria_id', 'INT NULL AFTER prioridad');
    add_column_if_not_exists($pdo, 'soportes', 'fecha_asignacion', 'DATETIME NULL AFTER usuario_creacion_id');
    add_column_if_not_exists($pdo, 'soportes', 'fecha_vencimiento', 'DATETIME NULL AFTER fecha_cierre');
    add_column_if_not_exists($pdo, 'soportes', 'notificacion_vencimiento_enviada', 'BOOLEAN DEFAULT FALSE AFTER fecha_vencimiento');
    add_column_if_not_exists($pdo, 'soportes', 'notificacion_vencimiento_fecha', 'DATETIME NULL AFTER notificacion_vencimiento_enviada');
    add_column_if_not_exists($pdo, 'soportes', 'solucion', 'TEXT NULL AFTER notificacion_vencimiento_fecha');
    add_column_if_not_exists($pdo, 'soportes', 'observaciones', 'TEXT NULL AFTER solucion');
    add_column_if_not_exists($pdo, 'soportes', 'firma', 'LONGTEXT NULL AFTER observaciones');
    
    // Asegurar que existan los nombres que el código usa para valoración
    add_column_if_not_exists($pdo, 'soportes', 'valoracion', "ENUM('excelente', 'bueno', 'regular', 'malo') NULL AFTER firma");
    add_column_if_not_exists($pdo, 'soportes', 'comentario_valoracion', 'TEXT NULL AFTER valoracion');
    add_column_if_not_exists($pdo, 'soportes', 'fecha_valoracion', 'DATETIME NULL AFTER comentario_valoracion');
    add_column_if_not_exists($pdo, 'soportes', 'tiempo_atencion_minutos', 'INT NULL AFTER fecha_valoracion');
    
    // --- TABLA: equipos ---
    echo "🔍 Sincronizando tabla 'equipos'...\n";
    add_column_if_not_exists($pdo, 'equipos', 'proveedor_rif', 'VARCHAR(30) NULL AFTER proveedor');
    add_column_if_not_exists($pdo, 'equipos', 'imagen', 'VARCHAR(255) NULL AFTER observaciones');

    // --- TABLA: bitacora_acciones ---
    echo "🔍 Sincronizando tabla 'bitacora_acciones'...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `bitacora_acciones` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `usuario_id` INT NULL,
        `username` VARCHAR(50) NOT NULL,
        `accion` VARCHAR(255) NOT NULL,
        `enlace_tipo` VARCHAR(50) NULL,
        `enlace_id` INT NULL,
        `entidad` VARCHAR(50) NULL,
        `entidad_id` INT NULL,
        `datos_anteriores` JSON NULL,
        `datos_nuevos` JSON NULL,
        `ip_address` VARCHAR(45) NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    add_column_if_not_exists($pdo, 'bitacora_acciones', 'entidad', 'VARCHAR(50) NULL AFTER enlace_id');
    add_column_if_not_exists($pdo, 'bitacora_acciones', 'entidad_id', 'INT NULL AFTER entidad');
    add_column_if_not_exists($pdo, 'bitacora_acciones', 'datos_anteriores', 'JSON NULL AFTER entidad_id');
    add_column_if_not_exists($pdo, 'bitacora_acciones', 'datos_nuevos', 'JSON NULL AFTER datos_anteriores');
    add_column_if_not_exists($pdo, 'bitacora_acciones', 'ip_address', 'VARCHAR(45) NULL AFTER datos_nuevos');

    // --- TABLA: empleados ---
    echo "🔍 Sincronizando tabla 'empleados'...\n";
    add_column_if_not_exists($pdo, 'empleados', 'cargo', 'VARCHAR(150) NULL AFTER cedula');

    // --- TABLA: usuarios ---
    echo "🔍 Sincronizando tabla 'usuarios'...\n";
    add_column_if_not_exists($pdo, 'usuarios', 'tema', "VARCHAR(20) DEFAULT 'light' AFTER rol");

    // --- CREAR TABLAS FALTANTES ---
    echo "🔍 Creando tablas faltantes...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `categorias` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `nombre` VARCHAR(100) NOT NULL,
      `descripcion` TEXT NULL,
      `icono` VARCHAR(50) DEFAULT 'bi-tools',
      `color` VARCHAR(20) DEFAULT '#6c757d',
      `activo` BOOLEAN DEFAULT TRUE,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Datos iniciales de categorías si está vacía
    $count = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO `categorias` (`nombre`, `descripcion`, `icono`, `color`) VALUES
          ('Hardware', 'Fallos de componentes físicos', 'bi-cpu', '#dc3545'),
          ('Software', 'Errores de programas y SO', 'bi-window', '#0d6efd'),
          ('Red', 'Conectividad e internet', 'bi-wifi', '#198754'),
          ('Impresora', 'Problemas de impresión', 'bi-printer', '#ffc107'),
          ('Periféricos', 'Mouse, teclado, monitor', 'bi-mouse', '#6c757d'),
          ('Otro', 'Otros problemas', 'bi-question-circle', '#6c757d')");
    }

    echo "✅ Sincronización finalizada con éxito.\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Función auxiliar para agregar columnas si no existen
 */
function add_column_if_not_exists($pdo, $table, $column, $definition) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if ($stmt->rowCount() === 0) {
            echo "   ➕ Agregando columna '$column' a '$table'...\n";
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
            return true;
        }
    } catch (Exception $e) {
        echo "   ⚠️ Warning en '$table.$column': " . $e->getMessage() . "\n";
    }
    return false;
}

/**
 * Crea las tablas esenciales si no se encontró el archivo de migración
 */
function createEssentialTables($pdo) {
    echo "   🔧 Creando tablas esenciales...\n";
    
    $tables = [
        "CREATE TABLE IF NOT EXISTS `usuarios` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `rol` ENUM('admin', 'tecnico', 'consultor') NOT NULL DEFAULT 'consultor',
            `tema` VARCHAR(20) DEFAULT 'light',
            `empleado_id` INT NULL,
            `departamento_id` INT NULL,
            `activo` BOOLEAN DEFAULT TRUE,
            `ultimo_acceso` DATETIME NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS `departamentos` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nombre` VARCHAR(100) NOT NULL,
            `descripcion` TEXT NULL,
            `ubicacion` VARCHAR(150) NULL,
            `telefono` VARCHAR(20) NULL,
            `email` VARCHAR(150) NULL,
            `jefe_area_id` INT NULL,
            `jefe_area_nombre` VARCHAR(150) NULL,
            `activo` BOOLEAN DEFAULT TRUE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS `empleados` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nombre` VARCHAR(100) NOT NULL,
            `apellido` VARCHAR(100) NULL,
            `cedula` VARCHAR(15) NULL,
            `cargo` VARCHAR(100) NULL,
            `email` VARCHAR(150) NULL,
            `telefono` VARCHAR(20) NULL,
            `departamento_id` INT NULL,
            `usuario_id` INT NULL,
            `activo` BOOLEAN DEFAULT TRUE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS `equipos` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `codigo_inventario` VARCHAR(50) NULL,
            `numero_serie` VARCHAR(100) NULL,
            `tipo` VARCHAR(50) NOT NULL,
            `marca` VARCHAR(100) NULL,
            `modelo` VARCHAR(100) NULL,
            `estado` VARCHAR(30) NOT NULL DEFAULT 'disponible',
            `procesador` VARCHAR(100) NULL,
            `memoria_ram` VARCHAR(50) NULL,
            `almacenamiento` VARCHAR(100) NULL,
            `sistema_operativo` VARCHAR(100) NULL,
            `direccion_ip` VARCHAR(45) NULL,
            `observaciones` TEXT NULL,
            `imagen` VARCHAR(255) NULL,
            `departamento_id` INT NULL,
            `empleado_id` INT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS `categorias` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nombre` VARCHAR(100) NOT NULL,
            `descripcion` TEXT NULL,
            `icono` VARCHAR(50) DEFAULT 'bi-tools',
            `color` VARCHAR(20) DEFAULT '#6c757d',
            `activo` BOOLEAN DEFAULT TRUE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS `soportes` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `fecha` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `titulo` VARCHAR(255) NULL,
            `descripcion` TEXT NOT NULL,
            `estado` ENUM('pendiente', 'asignado', 'en_proceso', 'resuelto', 'cerrado') NOT NULL DEFAULT 'pendiente',
            `prioridad` ENUM('baja', 'media', 'alta', 'critica') DEFAULT 'media',
            `categoria_id` INT NULL,
            `equipo_id` INT NOT NULL,
            `empleado_id` INT NULL,
            `usuario_creacion_id` INT NULL,
            `fecha_asignacion` DATETIME NULL,
            `fecha_cierre` DATETIME NULL,
            `fecha_vencimiento` DATETIME NULL,
            `solucion` TEXT NULL,
            `observaciones` TEXT NULL,
            `firma` LONGTEXT NULL,
            `valoracion` TINYINT NULL,
            `tiempo_atencion_minutos` INT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS `bitacora_acciones` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `usuario_id` INT NULL,
            `username` VARCHAR(50) NOT NULL,
            `accion` VARCHAR(255) NOT NULL,
            `enlace_tipo` VARCHAR(50) NULL,
            `enlace_id` INT NULL,
            `entidad` VARCHAR(50) NULL,
            `entidad_id` INT NULL,
            `datos_anteriores` JSON NULL,
            `datos_nuevos` JSON NULL,
            `ip_address` VARCHAR(45) NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
    
    foreach ($tables as $sql) {
        try {
            $pdo->exec($sql);
        } catch (Exception $e) {
            echo "   ⚠️ " . substr($e->getMessage(), 0, 60) . "\n";
        }
    }
    
    // Create admin user
    $adminHash = '$2y$10$UzcTNIBXYdAEBfdzynSn8uBfjd5Z5SELvQGOSWOcH/o.BaaaL88ja';
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = 'admin'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO `usuarios` (`username`, `password`, `rol`) VALUES ('admin', '$adminHash', 'admin')");
            echo "   ✅ Usuario admin creado (contraseña: admin123)\n";
        }
    } catch (Exception $e) { }
    
    // Insert default categories
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
        if ($count == 0) {
            $pdo->exec("INSERT INTO `categorias` (`nombre`, `descripcion`, `icono`, `color`) VALUES
              ('Hardware', 'Fallos de componentes físicos', 'bi-cpu', '#dc3545'),
              ('Software', 'Errores de programas y SO', 'bi-window', '#0d6efd'),
              ('Red', 'Conectividad e internet', 'bi-wifi', '#198754'),
              ('Impresora', 'Problemas de impresión', 'bi-printer', '#ffc107'),
              ('Periféricos', 'Mouse, teclado, monitor', 'bi-mouse', '#6c757d'),
              ('Otro', 'Otros problemas', 'bi-question-circle', '#6c757d')");
        }
    } catch (Exception $e) { }
    
    echo "   ✅ Tablas esenciales creadas\n";
}

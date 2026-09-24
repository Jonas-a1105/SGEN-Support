<?php
// 1. Cargar el autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Cargar variables de entorno (.env)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// 3. Configuración de Depuración (basado en .env)
if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// 4. Configurar zona horaria
date_default_timezone_set($_ENV['TIMEZONE'] ?? 'America/Caracas');

// 5. Iniciar Sesión (CRÍTICO: Debe ir antes de cualquier salida)
// Configurar duración de sesión (8 horas = 28800 segundos)
ini_set('session.gc_maxlifetime', 28800);
ini_set('session.cookie_lifetime', 28800);

// Force UTF-8 Encoding Globally
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
header('Content-Type: text/html; charset=utf-8');

session_start();

// Archivo de configuración principal (ahora puede usar $_ENV)
require_once __DIR__ . '/../config/config.php';

// 6. BOOTSTRAP: Verificar que las tablas existan antes de continuar
ensureDatabaseReady();

use App\Core\Router;

// Obtener la URL solicitada (usamos REQUEST_URI para obtener la ruta completa)
$uri = $_SERVER['REQUEST_URI'];
// Obtener el método (GET, POST)
$method = $_SERVER['REQUEST_METHOD'];

// Cargar las rutas y dirigir la solicitud
try {
    Router::load(__DIR__ . '/../config/routes.php')
          ->direct($uri, $method);
} catch (Exception $e) {
    // En producción, esto debería ir a un log
    echo "Error: " . $e->getMessage();
}

/**
 * Asegura que la base de datos esté lista con las tablas mínimas
 */
function ensureDatabaseReady() {
    try {
        $pdo = \App\Core\Database::getInstance()->getConnection();
        
        // Quick check: does soportes table exist?
        $stmt = $pdo->query("SHOW TABLES LIKE 'soportes'");
        if ($stmt->rowCount() > 0) {
            return; // All good
        }
        
        // Tables missing! Run bootstrap
        error_log("SGEN-Support: Tables missing, running bootstrap...");
        
        // Try to find and run the initial migration
        $migrationPaths = [
            __DIR__ . '/../migrations/m0000_00_00_initial_setup.php',
        ];
        
        $resourcesPath = getenv('RESOURCES_PATH') ?: ($_ENV['RESOURCES_PATH'] ?? null);
        if ($resourcesPath) {
            array_unshift($migrationPaths, $resourcesPath . '/migrations/m0000_00_00_initial_setup.php');
        }
        
        foreach ($migrationPaths as $migrationFile) {
            if (file_exists($migrationFile)) {
                $migration = require $migrationFile;
                if (is_array($migration) && isset($migration['up'])) {
                    $migration['up']($pdo);
                    return;
                }
            }
        }
        
        // Fallback: create essential tables inline
        createMinimalTables($pdo);
        
    } catch (Exception $e) {
        error_log("SGEN-Support Bootstrap Error: " . $e->getMessage());
    }
}

/**
 * Crea las tablas mínimas necesarias para que la aplicación funcione
 */
function createMinimalTables($pdo) {
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS `sesiones_log` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `usuario_id` INT NOT NULL,
            `username` VARCHAR(50) NOT NULL,
            `fecha_inicio` DATETIME NOT NULL,
            `fecha_fin` DATETIME NULL,
            `ip_address` VARCHAR(45) NULL,
            `user_agent` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS `notificaciones` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `usuario_id` INT NOT NULL,
            `titulo` VARCHAR(255) NOT NULL,
            `mensaje` TEXT NOT NULL,
            `tipo` VARCHAR(50) DEFAULT 'info',
            `enlace` VARCHAR(500) NULL,
            `leido` BOOLEAN DEFAULT FALSE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
    
    foreach ($tables as $sql) {
        try {
            $pdo->exec($sql);
        } catch (Exception $e) {
            error_log("Table creation error: " . $e->getMessage());
        }
    }
    
    // Create admin user
    $adminHash = '$2y$10$UzcTNIBXYdAEBfdzynSn8uBfjd5Z5SELvQGOSWOcH/o.BaaaL88ja';
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = 'admin'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO `usuarios` (`username`, `password`, `rol`) VALUES ('admin', '$adminHash', 'admin')");
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
}
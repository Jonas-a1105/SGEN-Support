<?php
/**
 * Migration: Initial Setup (Database Bootstrap)
 * 
 * This migration loads the complete schema.sql file on first installation.
 * It creates all tables, foreign keys, indexes, and default data.
 * 
 * For existing installations (updates), this migration safely skips
 * because all tables use IF NOT EXISTS.
 * 
 * Date: 2025-12-18
 */

return [
    'up' => function (PDO $pdo) {
        
        echo "   🔄 Verificando estado de la base de datos...\n";
        
        // Check if this is a fresh install by looking for the soportes table
        try {
            $result = $pdo->query("SHOW TABLES LIKE 'soportes'");
            $exists = $result->rowCount() > 0;
        } catch (Exception $e) {
            $exists = false;
        }
        
        if (!$exists) {
            // --- FIRST TIME INSTALLATION ---
            echo "   🆕 Primera instalación detectada. Buscando dump de base de datos...\n";
            
            $schemaFile = findSchemaFile();
            if ($schemaFile) {
                echo "   📄 Usando archivo SQL: " . basename($schemaFile) . "\n";
                
                // Leer archivo con codificación UTF-8 explícita
                $sql = file_get_contents($schemaFile);
                
                // Asegurar que está en UTF-8
                if (!mb_check_encoding($sql, 'UTF-8')) {
                    $sql = mb_convert_encoding($sql, 'UTF-8', 'auto');
                }
                
                // Limpiar comentarios de MariaDB si existen (sandbox mode etc)
                $sql = preg_replace('/\/\*M!.*?\*\//s', '', $sql);
                
                try {
                    // Configurar conexión para UTF-8
                    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $pdo->exec("SET CHARACTER SET utf8mb4");
                    $pdo->exec("SET character_set_connection = utf8mb4");
                    $pdo->exec("SET character_set_results = utf8mb4");
                    $pdo->exec("SET collation_connection = utf8mb4_unicode_ci");
                    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
                    $pdo->exec($sql);
                    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
                    echo "   ✅ Estructura y datos cargados desde el archivo SQL.\n";
                } catch (Exception $e) {
                    echo "   ⚠️ Error cargando SQL directo: " . $e->getMessage() . "\n";
                    echo "   🔧 Reintentando con creación manual...\n";
                    createTablesManually($pdo);
                }
            } else {
                // Si no hay archivo SQL, usar fallback manual
                createTablesManually($pdo);
            }

            // Sincronizar seeds solo en la primera instalación
            importSeedsIfAvailable($pdo);
        } else {
            echo "   ℹ️  Base de datos ya existe.\n";
        }
        
        // Ensure admin user exists
        ensureAdminUser($pdo);
        
        echo "   ✅ Base de datos sincronizada correctamente\n";
    },
    
    'down' => function (PDO $pdo) {
        echo "   ⚠️  No se puede revertir el setup inicial (protección de datos)\n";
    }
];

/**
 * Find the schema.sql file in various possible locations
 */
function findSchemaFile(): ?string {
    $resourcesPath = getenv('RESOURCES_PATH') ?: ($_ENV['RESOURCES_PATH'] ?? null);
    
    $possiblePaths = [];
    
    if ($resourcesPath) {
        $possiblePaths[] = $resourcesPath . '/database/sgen_db_fixed.sql';
        $possiblePaths[] = $resourcesPath . '/database/sgen_db.sql';
        echo "   📁 RESOURCES_PATH detectado: $resourcesPath\n";
    }
    
    // Rutas relativas al script
    $possiblePaths[] = __DIR__ . '/../database/sgen_db_fixed.sql';
    $possiblePaths[] = __DIR__ . '/../database/sgen_db.sql';
    $possiblePaths[] = __DIR__ . '/../../database/sgen_db_fixed.sql';
    $possiblePaths[] = __DIR__ . '/../../database/sgen_db.sql';
    $possiblePaths[] = dirname(__DIR__) . '/database/sgen_db_fixed.sql';

    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            echo "   🔍 Buscando SQL en: $path -> ✅ ENCONTRADO\n";
            return $path;
        } else {
            // echo "   🔍 Buscando SQL en: $path -> ❌\n";
        }
    }
    
    return null;
}

/**
 * Remove SQL comments
 */
function removeComments(string $sql): string {
    // Remove multi-line comments
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    // Remove single-line comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    return $sql;
}

/**
 * Split SQL into individual statements
 */
function splitStatements(string $sql): array {
    // Simple split by semicolon (works for most cases)
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    return $statements;
}

/**
 * Ensure admin user exists
 */
function ensureAdminUser(PDO $pdo): void {
    // Password: admin123 (bcrypt hash)
    $adminHash = '$2y$10$UzcTNIBXYdAEBfdzynSn8uBfjd5Z5SELvQGOSWOcH/o.BaaaL88ja';
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = 'admin'");
        $stmt->execute();
        $exists = $stmt->fetchColumn() > 0;
        
        if (!$exists) {
            $pdo->exec("INSERT INTO `usuarios` (`username`, `password`, `rol`) VALUES ('admin', '$adminHash', 'admin')");
            echo "   ✅ Usuario admin creado (contraseña: admin123)\n";
        } else {
            echo "   ℹ️  Usuario admin ya existe\n";
        }
    } catch (Exception $e) {
        echo "   ⚠️  No se pudo verificar usuario admin: " . $e->getMessage() . "\n";
    }
}

/**
 * Import seed data FORCING overwrite of existing data
 * This ensures all users have the same database content
 */
function importSeedsIfAvailable(PDO $pdo): void {
    // Find seeds file
    $seedsPaths = [
        __DIR__ . '/../database/seeds.php',
        dirname(__DIR__) . '/database/seeds.php',
    ];
    
    $resourcesPath = getenv('RESOURCES_PATH') ?: ($_ENV['RESOURCES_PATH'] ?? null);
    if ($resourcesPath) {
        array_unshift($seedsPaths, $resourcesPath . '/database/seeds.php');
    }
    
    $seedsFile = null;
    foreach ($seedsPaths as $path) {
        if (file_exists($path)) {
            $seedsFile = $path;
            break;
        }
    }
    
    if (!$seedsFile) {
        echo "   ℹ️  No se encontró archivo seeds.php (instalación vacía)\n";
        return;
    }
    
    echo "   📦 Sincronizando base de datos con datos del instalador...\n";
    
    try {
        $seeds = require $seedsFile;
        
        if (!is_array($seeds)) {
            return;
        }
        
        // Disable foreign key checks temporarily for truncate
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        $totalImported = 0;
        foreach ($seeds as $table => $rows) {
            if (empty($rows)) continue;
            
            try {
                // Check if table exists
                $check = $pdo->query("SHOW TABLES LIKE '$table'");
                if ($check->rowCount() === 0) {
                    continue; // Table doesn't exist, skip
                }
                
                // TRUNCATE the table to remove old data
                $pdo->exec("TRUNCATE TABLE `$table`");
                echo "   🔄 Sincronizando tabla '$table'...\n";
                
                // Import all rows
                foreach ($rows as $row) {
                    try {
                        $columns = array_keys($row);
                        $placeholders = array_fill(0, count($columns), '?');
                        
                        $sql = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $placeholders) . ")";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute(array_values($row));
                        $totalImported++;
                    } catch (Exception $e) {
                        // Log but continue
                        echo "   ⚠️ Error en registro: " . substr($e->getMessage(), 0, 50) . "\n";
                    }
                }
            } catch (Exception $e) {
                echo "   ⚠️ Error en tabla '$table': " . $e->getMessage() . "\n";
            }
        }
        
        // Re-enable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        if ($totalImported > 0) {
            echo "   ✅ $totalImported registros sincronizados\n";
        }
        
    } catch (Exception $e) {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1"); // Ensure we re-enable
        echo "   ⚠️  Error importando seeds: " . $e->getMessage() . "\n";
    }
}

/**
 * Create tables manually if schema.sql is not found
 * This is a fallback for edge cases
 */
function createTablesManually(PDO $pdo): void {
    echo "   🔧 Creando estructura base manualmente...\n";
    
    // Create essential tables that the app needs to start
    $tables = [
        // usuarios
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
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // departamentos
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
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // empleados
        "CREATE TABLE IF NOT EXISTS `empleados` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nombre` VARCHAR(100) NOT NULL,
            `apellido` VARCHAR(100) NOT NULL,
            `email` VARCHAR(150) NOT NULL,
            `cedula` VARCHAR(15) NULL,
            `cargo` VARCHAR(150) NULL,
            `departamento_id` INT NULL,
            `rol` ENUM('tecnico', 'administrador', 'consultor') NOT NULL DEFAULT 'tecnico',
            `usuario_id` INT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // equipos
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
            `driver` VARCHAR(150) NULL,
            `toner` VARCHAR(100) NULL,
            `ubicacion_fisica` VARCHAR(150) NULL,
            `fecha_compra` DATE NULL,
            `garantia` DATE NULL,
            `valor_compra` DECIMAL(12,2) NULL,
            `proveedor` VARCHAR(150) NULL,
            `proveedor_rif` VARCHAR(30) NULL,
            `observaciones` TEXT NULL,
            `imagen` VARCHAR(255) NULL,
            `departamento_id` INT NULL,
            `empleado_id` INT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // categorias
        "CREATE TABLE IF NOT EXISTS `categorias` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nombre` VARCHAR(100) NOT NULL,
            `descripcion` TEXT NULL,
            `icono` VARCHAR(50) DEFAULT 'bi-tools',
            `color` VARCHAR(20) DEFAULT '#6c757d',
            `activo` BOOLEAN DEFAULT TRUE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // soportes
        "CREATE TABLE IF NOT EXISTS `soportes` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `fecha` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `titulo` VARCHAR(255) NULL,
            `descripcion` TEXT NOT NULL,
            `observaciones` TEXT NULL,
            `firma` LONGTEXT NULL,
            `estado` ENUM('pendiente', 'en_proceso', 'en_espera', 'resuelto') NOT NULL DEFAULT 'pendiente',
            `prioridad` ENUM('baja', 'media', 'alta', 'critica') DEFAULT 'media',
            `fecha_vencimiento` DATETIME NULL,
            `notificacion_vencimiento_enviada` BOOLEAN DEFAULT FALSE,
            `notificacion_vencimiento_fecha` DATETIME NULL,
            `solucion` TEXT NULL,
            `equipo_id` INT NOT NULL,
            `categoria_id` INT NULL,
            `empleado_id` INT NULL,
            `usuario_creacion_id` INT NULL,
            `fecha_asignacion` DATETIME NULL,
            `fecha_cierre` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `tiempo_atencion_minutos` INT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `valoracion` ENUM('excelente', 'bueno', 'regular', 'malo') NULL,
            `valoracion_comentario` TEXT NULL,
            `valoracion_fecha` DATETIME NULL,
            `comentario_valoracion` TEXT NULL,
            `fecha_valoracion` DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // mantenimientos
        "CREATE TABLE IF NOT EXISTS `mantenimientos` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `equipo_id` INT NOT NULL,
            `tipo_mantenimiento` ENUM('preventivo', 'correctivo', 'predictivo') NOT NULL DEFAULT 'preventivo',
            `estado` ENUM('pendiente', 'en_proceso', 'completado', 'pospuesto', 'cancelado') NOT NULL DEFAULT 'pendiente',
            `descripcion` TEXT NOT NULL,
            `fecha` DATETIME NOT NULL,
            `proxima_fecha` DATE NULL,
            `costo` DECIMAL(10,2) DEFAULT 0.00,
            `realizado_por` VARCHAR(150) NULL,
            `tecnico_id` INT NULL,
            `checklist` JSON NULL,
            `observaciones` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // inventario_items
        "CREATE TABLE IF NOT EXISTS `inventario_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `codigo` VARCHAR(50) NULL,
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
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // inventario_movimientos
        "CREATE TABLE IF NOT EXISTS `inventario_movimientos` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `item_id` INT NOT NULL,
            `tipo_movimiento` ENUM('ENTRADA', 'SALIDA', 'AJUSTE', 'TRANSFERENCIA', 'CONSUMO', 'BAJA') NOT NULL,
            `cantidad` INT NOT NULL,
            `motivo` TEXT NULL,
            `usuario_id` INT NULL,
            `origen_departamento_id` INT NULL,
            `destino_departamento_id` INT NULL,
            `referencia_tipo` VARCHAR(50) NULL,
            `referencia_id` INT NULL,
            `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // ticket_comentarios
        "CREATE TABLE IF NOT EXISTS `ticket_comentarios` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `ticket_id` INT NOT NULL,
            `usuario_id` INT NOT NULL,
            `comentario` TEXT NOT NULL,
            `es_interno` BOOLEAN DEFAULT FALSE,
            `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // ticket_archivos
        "CREATE TABLE IF NOT EXISTS `ticket_archivos` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `ticket_id` INT NOT NULL,
            `nombre_archivo` VARCHAR(255) NOT NULL,
            `nombre_original` VARCHAR(255) NOT NULL,
            `ruta` VARCHAR(500) NOT NULL,
            `tipo_mime` VARCHAR(100) NULL,
            `tamaño_bytes` INT NULL,
            `subido_por` INT NULL,
            `fecha_subida` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // sesiones_log
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
        
        // notificaciones
        "CREATE TABLE IF NOT EXISTS `notificaciones` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `usuario_id` INT NOT NULL,
            `titulo` VARCHAR(255) NOT NULL,
            `mensaje` TEXT NOT NULL,
            `tipo` VARCHAR(50) DEFAULT 'info',
            `enlace` VARCHAR(500) NULL,
            `leido` BOOLEAN DEFAULT FALSE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // bitacora_acciones
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
        
        // inventario_ubicaciones
        "CREATE TABLE IF NOT EXISTS `inventario_ubicaciones` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `item_id` INT NOT NULL,
            `departamento_id` INT NULL,
            `cantidad` INT NOT NULL DEFAULT 0,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // inventario_consumos
        "CREATE TABLE IF NOT EXISTS `inventario_consumos` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `soporte_id` INT NOT NULL,
            `item_id` INT NOT NULL,
            `cantidad` INT NOT NULL,
            `usuario_id` INT NOT NULL,
            `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // bajas_inventario
        "CREATE TABLE IF NOT EXISTS `bajas_inventario` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `inventario_id` INT NOT NULL,
            `cantidad` INT NOT NULL,
            `motivo` TEXT NULL,
            `fecha` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `usuario_id` INT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
    
    foreach ($tables as $sql) {
        try {
            $pdo->exec($sql);
        } catch (Exception $e) {
            echo "   ⚠️  " . substr($e->getMessage(), 0, 60) . "\n";
        }
    }
    
    // Insert default categories
    try {
        $pdo->exec("INSERT IGNORE INTO `categorias` (`nombre`, `descripcion`, `icono`, `color`) VALUES
            ('Hardware', 'Fallos de componentes físicos', 'bi-cpu', '#dc3545'),
            ('Software', 'Errores de programas y SO', 'bi-window', '#0d6efd'),
            ('Red', 'Conectividad e internet', 'bi-wifi', '#198754'),
            ('Impresora', 'Problemas de impresión', 'bi-printer', '#ffc107'),
            ('Periféricos', 'Mouse, teclado, monitor', 'bi-mouse', '#6c757d'),
            ('Otro', 'Otros problemas', 'bi-question-circle', '#6c757d')
        ");
    } catch (Exception $e) { }
    
    // Create admin user
    ensureAdminUser($pdo);
    
    echo "   ✅ Estructura base creada\n";
}

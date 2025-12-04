<?php
/**
 * Migración: Mejorar tabla de mantenimientos
 * Agrega campos para mantenimientos programados
 */

require_once __DIR__ . '/../src/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "Iniciando migración de mantenimientos...\n";
    
    // Verificar si la tabla existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'mantenimientos'");
    if ($stmt->rowCount() == 0) {
        // Crear tabla desde cero
        echo "Creando tabla mantenimientos...\n";
        $pdo->exec("
            CREATE TABLE mantenimientos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                equipo_id INT NOT NULL,
                tipo_mantenimiento ENUM('preventivo', 'correctivo', 'predictivo') NOT NULL DEFAULT 'preventivo',
                estado ENUM('pendiente', 'en_proceso', 'completado', 'pospuesto', 'cancelado') NOT NULL DEFAULT 'pendiente',
                descripcion TEXT NOT NULL,
                fecha DATETIME NOT NULL,
                proxima_fecha DATE NULL,
                frecuencia ENUM('unica', 'mensual', 'trimestral', 'semestral', 'anual') DEFAULT 'unica',
                costo DECIMAL(10,2) DEFAULT 0.00,
                realizado_por VARCHAR(255) NULL,
                tecnico_id INT NULL,
                checklist JSON NULL,
                observaciones TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON DELETE CASCADE,
                INDEX idx_equipo (equipo_id),
                INDEX idx_estado (estado),
                INDEX idx_proxima_fecha (proxima_fecha)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✓ Tabla mantenimientos creada\n";
    } else {
        // Actualizar tabla existente
        echo "Actualizando tabla mantenimientos...\n";
        
        // Agregar columnas nuevas si no existen
        $columnas = [
            "ALTER TABLE mantenimientos ADD COLUMN IF NOT EXISTS estado ENUM('pendiente', 'en_proceso', 'completado', 'pospuesto', 'cancelado') NOT NULL DEFAULT 'pendiente' AFTER tipo_mantenimiento",
            "ALTER TABLE mantenimientos ADD COLUMN IF NOT EXISTS proxima_fecha DATE NULL AFTER fecha",
            "ALTER TABLE mantenimientos ADD COLUMN IF NOT EXISTS frecuencia ENUM('unica', 'mensual', 'trimestral', 'semestral', 'anual') DEFAULT 'unica' AFTER proxima_fecha",
            "ALTER TABLE mantenimientos ADD COLUMN IF NOT EXISTS tecnico_id INT NULL AFTER realizado_por",
            "ALTER TABLE mantenimientos ADD COLUMN IF NOT EXISTS checklist JSON NULL AFTER tecnico_id",
            "ALTER TABLE mantenimientos ADD COLUMN IF NOT EXISTS observaciones TEXT NULL AFTER checklist",
            "ALTER TABLE mantenimientos ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
            "ALTER TABLE mantenimientos ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
        ];
        
        foreach ($columnas as $sql) {
            try {
                $pdo->exec($sql);
                echo "✓ Columna agregada\n";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                    echo "⚠ " . $e->getMessage() . "\n";
                }
            }
        }
        
        // Modificar tipo_mantenimiento si es necesario
        try {
            $pdo->exec("ALTER TABLE mantenimientos MODIFY COLUMN tipo_mantenimiento ENUM('preventivo', 'correctivo', 'predictivo') NOT NULL DEFAULT 'preventivo'");
            echo "✓ Tipo de mantenimiento actualizado\n";
        } catch (PDOException $e) {
            echo "⚠ " . $e->getMessage() . "\n";
        }
        
        // Agregar índices
        try {
            $pdo->exec("ALTER TABLE mantenimientos ADD INDEX IF NOT EXISTS idx_estado (estado)");
            $pdo->exec("ALTER TABLE mantenimientos ADD INDEX IF NOT EXISTS idx_proxima_fecha (proxima_fecha)");
            echo "✓ Índices agregados\n";
        } catch (PDOException $e) {
            // Ignorar si ya existen
        }
    }
    
    echo "\n✓ Migración completada exitosamente!\n";
    
} catch (PDOException $e) {
    echo "❌ Error en la migración: " . $e->getMessage() . "\n";
    exit(1);
}

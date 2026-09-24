<?php
/**
 * Migration: Ensure bitacora_acciones table exists
 * 
 * Date: 2025-12-22
 */

return [
    'up' => function (PDO $pdo) {
        echo "   🔄 Verificando tabla bitacora_acciones...\n";
        
        try {
            $createTableSql = "CREATE TABLE IF NOT EXISTS `bitacora_acciones` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

            $pdo->exec($createTableSql);
            echo "   ✅ Tabla bitacora_acciones asegurada.\n";
            
            // Ensure all columns exist (in case it was created with an old schema)
            $columns = [
                'enlace_tipo' => 'VARCHAR(50) NULL AFTER `accion`',
                'enlace_id' => 'INT NULL AFTER `enlace_tipo`',
                'entidad' => 'VARCHAR(50) NULL AFTER `enlace_id`',
                'entidad_id' => 'INT NULL AFTER `entidad`',
                'datos_anteriores' => 'JSON NULL AFTER `entidad_id`',
                'datos_nuevos' => 'JSON NULL AFTER `datos_anteriores`',
                'ip_address' => 'VARCHAR(45) NULL AFTER `datos_nuevos`'
            ];

            foreach ($columns as $col => $def) {
                $stmt = $pdo->query("SHOW COLUMNS FROM bitacora_acciones LIKE '$col'");
                if ($stmt->rowCount() === 0) {
                    echo "   ➕ Agregando columna '$col'...\n";
                    $pdo->exec("ALTER TABLE bitacora_acciones ADD COLUMN `$col` $def");
                }
            }

        } catch (Exception $e) {
            echo "   ❌ Error en aseguramiento de bitacora: " . $e->getMessage() . "\n";
        }
    },
    
    'down' => function (PDO $pdo) {
        // No destructivo en down por seguridad
        echo "   ℹ️ Down migration skipped for safety.\n";
    }
];

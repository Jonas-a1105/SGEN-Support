<?php
/**
 * Migration: Add enlace_tipo and enlace_id to bitacora_acciones
 * 
 * Date: 2025-12-18
 */

return [
    'up' => function (PDO $pdo) {
        echo "   🔄 Actualizando tabla bitacora_acciones...\n";
        
        try {
            // Check enlace_tipo
            $result = $pdo->query("SHOW COLUMNS FROM bitacora_acciones LIKE 'enlace_tipo'");
            if ($result->rowCount() === 0) {
                echo "   ➕ Añadiendo columna 'enlace_tipo'...\n";
                $pdo->exec("ALTER TABLE bitacora_acciones ADD COLUMN `enlace_tipo` VARCHAR(50) NULL AFTER `accion`");
            }

            // Check enlace_id
            $result = $pdo->query("SHOW COLUMNS FROM bitacora_acciones LIKE 'enlace_id'");
            if ($result->rowCount() === 0) {
                echo "   ➕ Añadiendo columna 'enlace_id'...\n";
                $pdo->exec("ALTER TABLE bitacora_acciones ADD COLUMN `enlace_id` INT NULL AFTER `enlace_tipo`");
            }
            
            echo "   ✅ Columnas de enlace añadidas correctamente\n";
        } catch (Exception $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
        }
    },
    
    'down' => function (PDO $pdo) {
        echo "   🔄 Revirtiendo cambios en bitacora_acciones...\n";
        try {
            $pdo->exec("ALTER TABLE bitacora_acciones DROP COLUMN `enlace_tipo`, DROP COLUMN `enlace_id`");
            echo "   ✅ Columnas eliminadas\n";
        } catch (Exception $e) {
            echo "   ⚠️  No se pudo revertir: " . $e->getMessage() . "\n";
        }
    }
];

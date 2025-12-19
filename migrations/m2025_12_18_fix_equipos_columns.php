<?php
/**
 * Migration: Fix equipos table columns
 * 
 * This migration fixes column name mismatches for existing installations:
 * - Renames 'ram' to 'memoria_ram' (if ram exists)
 * - Renames 'fecha_garantia' to 'garantia' (if fecha_garantia exists)
 * - Adds 'proveedor_rif' column (if missing)
 * 
 * Date: 2025-12-18
 */

return [
    'up' => function (PDO $pdo) {
        echo "   🔄 Verificando columnas de tabla equipos...\n";
        
        // Check and rename 'ram' to 'memoria_ram'
        try {
            $result = $pdo->query("SHOW COLUMNS FROM equipos LIKE 'ram'");
            if ($result->rowCount() > 0) {
                echo "   ↔️  Renombrando 'ram' a 'memoria_ram'...\n";
                $pdo->exec("ALTER TABLE equipos CHANGE COLUMN `ram` `memoria_ram` VARCHAR(50) NULL");
                echo "   ✅ Columna renombrada\n";
            }
        } catch (Exception $e) {
            // Column might not exist or already renamed
        }
        
        // Check and rename 'fecha_garantia' to 'garantia'
        try {
            $result = $pdo->query("SHOW COLUMNS FROM equipos LIKE 'fecha_garantia'");
            if ($result->rowCount() > 0) {
                echo "   ↔️  Renombrando 'fecha_garantia' a 'garantia'...\n";
                $pdo->exec("ALTER TABLE equipos CHANGE COLUMN `fecha_garantia` `garantia` DATE NULL");
                echo "   ✅ Columna renombrada\n";
            }
        } catch (Exception $e) {
            // Column might not exist or already renamed
        }
        
        // Check and add 'proveedor_rif' if missing
        try {
            $result = $pdo->query("SHOW COLUMNS FROM equipos LIKE 'proveedor_rif'");
            if ($result->rowCount() === 0) {
                echo "   ➕ Añadiendo columna 'proveedor_rif'...\n";
                $pdo->exec("ALTER TABLE equipos ADD COLUMN `proveedor_rif` VARCHAR(30) NULL AFTER `proveedor`");
                echo "   ✅ Columna añadida\n";
            }
        } catch (Exception $e) {
            echo "   ⚠️  " . substr($e->getMessage(), 0, 60) . "\n";
        }
        
        // Ensure 'memoria_ram' exists (for fresh installs that might have issues)
        try {
            $result = $pdo->query("SHOW COLUMNS FROM equipos LIKE 'memoria_ram'");
            if ($result->rowCount() === 0) {
                echo "   ➕ Añadiendo columna 'memoria_ram'...\n";
                $pdo->exec("ALTER TABLE equipos ADD COLUMN `memoria_ram` VARCHAR(50) NULL AFTER `procesador`");
                echo "   ✅ Columna añadida\n";
            }
        } catch (Exception $e) { }
        
        // Ensure 'garantia' exists
        try {
            $result = $pdo->query("SHOW COLUMNS FROM equipos LIKE 'garantia'");
            if ($result->rowCount() === 0) {
                echo "   ➕ Añadiendo columna 'garantia'...\n";
                $pdo->exec("ALTER TABLE equipos ADD COLUMN `garantia` DATE NULL AFTER `fecha_compra`");
                echo "   ✅ Columna añadida\n";
            }
        } catch (Exception $e) { }
        
        echo "   ✅ Verificación de columnas completada\n";
    },
    
    'down' => function (PDO $pdo) {
        echo "   ⚠️  No se revierte esta migración\n";
    }
];

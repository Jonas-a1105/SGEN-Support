<?php
/**
 * Migration: Add imagen column to equipos table
 * 
 * Date: 2025-12-18
 */

return [
    'up' => function (PDO $pdo) {
        echo "   🔄 Agregando columna 'imagen' a tabla equipos...\n";
        
        try {
            $result = $pdo->query("SHOW COLUMNS FROM equipos LIKE 'imagen'");
            if ($result->rowCount() === 0) {
                echo "   ➕ Añadiendo columna 'imagen'...\n";
                // Adding it after 'tipo' or 'modelo' or at the end
                $pdo->exec("ALTER TABLE equipos ADD COLUMN `imagen` VARCHAR(255) NULL AFTER `modelo`");
                echo "   ✅ Columna añadida correctamente\n";
            } else {
                echo "   ℹ️  La columna 'imagen' ya existe.\n";
            }
        } catch (Exception $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
        }
    },
    
    'down' => function (PDO $pdo) {
        echo "   🔄 Eliminando columna 'imagen' de tabla equipos...\n";
        try {
            $pdo->exec("ALTER TABLE equipos DROP COLUMN `imagen`");
            echo "   ✅ Columna eliminada\n";
        } catch (Exception $e) {
            echo "   ⚠️  No se pudo eliminar: " . $e->getMessage() . "\n";
        }
    }
];

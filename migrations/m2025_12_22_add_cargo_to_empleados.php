<?php
/**
 * Migration: Add cargo column to empleados table
 * 
 * Date: 2025-12-22
 */

return [
    'up' => function (PDO $pdo) {
        echo "   🔄 Agregando columna 'cargo' a tabla empleados...\n";
        
        try {
            $result = $pdo->query("SHOW COLUMNS FROM empleados LIKE 'cargo'");
            if ($result->rowCount() === 0) {
                echo "   ➕ Añadiendo columna 'cargo'...\n";
                $pdo->exec("ALTER TABLE empleados ADD COLUMN `cargo` VARCHAR(150) DEFAULT NULL AFTER `cedula`");
                echo "   ✅ Columna añadida correctamente\n";
            } else {
                echo "   ℹ️  La columna 'cargo' ya existe.\n";
            }
        } catch (Exception $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
        }
    },
    
    'down' => function (PDO $pdo) {
        echo "   🔄 Eliminando columna 'cargo' de tabla empleados...\n";
        try {
            $pdo->exec("ALTER TABLE empleados DROP COLUMN `cargo`");
            echo "   ✅ Columna eliminada\n";
        } catch (Exception $e) {
            echo "   ⚠️  No se pudo eliminar: " . $e->getMessage() . "\n";
        }
    }
];

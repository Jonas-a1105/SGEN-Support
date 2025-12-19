<?php
/**
 * Migration: Add tema column to usuarios table
 * 
 * Date: 2025-12-18
 */

return [
    'up' => function (PDO $pdo) {
        echo "   🔄 Agregando columna 'tema' a tabla usuarios...\n";
        
        try {
            $result = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'tema'");
            if ($result->rowCount() === 0) {
                echo "   ➕ Añadiendo columna 'tema'...\n";
                $pdo->exec("ALTER TABLE usuarios ADD COLUMN `tema` VARCHAR(20) DEFAULT 'light' AFTER `rol`");
                echo "   ✅ Columna añadida correctamente\n";
            } else {
                echo "   ℹ️  La columna 'tema' ya existe.\n";
            }
        } catch (Exception $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
        }
    },
    
    'down' => function (PDO $pdo) {
        echo "   🔄 Eliminando columna 'tema' de tabla usuarios...\n";
        try {
            $pdo->exec("ALTER TABLE usuarios DROP COLUMN `tema`");
            echo "   ✅ Columna eliminada\n";
        } catch (Exception $e) {
            echo "   ⚠️  No se pudo eliminar: " . $e->getMessage() . "\n";
        }
    }
];

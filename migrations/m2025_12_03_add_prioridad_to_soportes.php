<?php
/**
 * Migración: Agregar campo de prioridad a tickets
 * Fecha: 2025-12-03
 * 
 * Permite clasificar tickets por prioridad: Alta, Media, Baja
 */

return [
    'up' => function (PDO $pdo) {
        // Verificar si la columna ya existe
        $checkSql = "SHOW COLUMNS FROM soportes LIKE 'prioridad'";
        $stmt = $pdo->query($checkSql);
        
        if ($stmt->rowCount() > 0) {
            echo "⚠ La columna 'prioridad' ya existe. Saltando migración.\n";
            return;
        }
        
        // Agregar columna prioridad
        $sql = "ALTER TABLE soportes 
                ADD COLUMN prioridad ENUM('alta', 'media', 'baja') DEFAULT 'media' 
                AFTER estado";
        $pdo->exec($sql);
        
        echo "✓ Columna 'prioridad' agregada a tabla 'soportes'.\n";
        echo "✓ Los tickets ahora pueden clasificarse por prioridad.\n";
    },
    
    'down' => function (PDO $pdo) {
        $sql = "ALTER TABLE soportes DROP COLUMN prioridad";
        $pdo->exec($sql);
        
        echo "Migración revertida: columna 'prioridad' eliminada de soportes.\n";
    }
];

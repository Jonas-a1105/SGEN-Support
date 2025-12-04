<?php
/**
 * Migración: Agregar campo de observaciones técnicas a tabla soportes
 * Fecha: 2025-12-03
 * 
 * Permite a los técnicos documentar lo que realmente hicieron
 * al atender un ticket, independiente de la descripción inicial.
 */

return [
    'up' => function (PDO $pdo) {
        // Verificar si la columna ya existe
        $checkSql = "SHOW COLUMNS FROM soportes LIKE 'observaciones'";
        $stmt = $pdo->query($checkSql);
        
        if ($stmt->rowCount() > 0) {
            echo "⚠ La columna 'observaciones' ya existe. Saltando migración.\n";
            return;
        }
        
        // Agregar columna observaciones después de descripcion
        $sql = "ALTER TABLE soportes 
                ADD COLUMN observaciones TEXT NULL 
                AFTER descripcion";
        $pdo->exec($sql);
        
        echo "✓ Columna 'observaciones' agregada a tabla 'soportes'.\n";
        echo "✓ Los técnicos ahora pueden documentar sus acciones en cada ticket.\n";
    },
    
    'down' => function (PDO $pdo) {
        $sql = "ALTER TABLE soportes DROP COLUMN observaciones";
        $pdo->exec($sql);
        
        echo "Migración revertida: columna 'observaciones' eliminada de soportes.\n";
    }
];

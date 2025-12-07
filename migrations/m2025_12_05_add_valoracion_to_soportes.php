<?php
/**
 * Migración: Agregar campos de valoración a soportes
 * Fecha: 2025-12-05
 * 
 * Permite que los usuarios valoren el servicio de soporte recibido
 */

return [
    'up' => function (PDO $pdo) {
        // Verificar si la columna ya existe
        $checkSql = "SHOW COLUMNS FROM soportes LIKE 'valoracion'";
        $stmt = $pdo->query($checkSql);
        
        if ($stmt->rowCount() > 0) {
            echo "⚠ La columna 'valoracion' ya existe. Saltando migración.\n";
            return;
        }
        
        // Agregar columnas de valoración
        $sql = "ALTER TABLE soportes 
                ADD COLUMN valoracion ENUM('excelente', 'bueno', 'regular', 'malo') NULL DEFAULT NULL,
                ADD COLUMN comentario_valoracion TEXT NULL DEFAULT NULL,
                ADD COLUMN fecha_valoracion DATETIME NULL DEFAULT NULL";
        $pdo->exec($sql);
        
        echo "✓ Columnas de valoración agregadas a tabla 'soportes'.\n";
        echo "✓ Los usuarios ahora pueden valorar el servicio de soporte.\n";
    },
    
    'down' => function (PDO $pdo) {
        $sql = "ALTER TABLE soportes 
                DROP COLUMN valoracion,
                DROP COLUMN comentario_valoracion,
                DROP COLUMN fecha_valoracion";
        $pdo->exec($sql);
        
        echo "Migración revertida: columnas de valoración eliminadas de soportes.\n";
    }
];

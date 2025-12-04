<?php
/**
 * Migración: Agregar columna fecha_vencimiento a soportes
 * Fecha: 2025-12-03
 */

return [
    'up' => function (PDO $pdo) {
        $checkCol = $pdo->query("SHOW COLUMNS FROM soportes LIKE 'fecha_vencimiento'");
        if ($checkCol->rowCount() == 0) {
            $sql = "ALTER TABLE soportes 
                    ADD COLUMN fecha_vencimiento DATETIME NULL AFTER prioridad";
            $pdo->exec($sql);
            echo "✓ Columna 'fecha_vencimiento' agregada a 'soportes'.\n";
        } else {
            echo "ℹ️ La columna 'fecha_vencimiento' ya existe.\n";
        }
    },

    'down' => function (PDO $pdo) {
        try {
            $pdo->exec("ALTER TABLE soportes DROP COLUMN fecha_vencimiento");
            echo "✓ Columna 'fecha_vencimiento' eliminada.\n";
        } catch (Exception $e) {
            echo "ℹ️ No se pudo eliminar la columna (quizás no existía).\n";
        }
    }
];

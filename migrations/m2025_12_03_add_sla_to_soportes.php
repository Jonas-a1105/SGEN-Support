<?php
/**
 * Migración: Agregar columna tiempo_atencion_minutos a soportes
 * Fecha: 2025-12-03
 */

return [
    'up' => function (PDO $pdo) {
        $checkCol = $pdo->query("SHOW COLUMNS FROM soportes LIKE 'tiempo_atencion_minutos'");
        if ($checkCol->rowCount() == 0) {
            $sql = "ALTER TABLE soportes 
                    ADD COLUMN tiempo_atencion_minutos INT NULL AFTER fecha_cierre";
            $pdo->exec($sql);
            echo "✓ Columna 'tiempo_atencion_minutos' agregada a 'soportes'.\n";
        } else {
            echo "ℹ️ La columna 'tiempo_atencion_minutos' ya existe.\n";
        }
    },

    'down' => function (PDO $pdo) {
        try {
            $pdo->exec("ALTER TABLE soportes DROP COLUMN tiempo_atencion_minutos");
            echo "✓ Columna 'tiempo_atencion_minutos' eliminada.\n";
        } catch (Exception $e) {
            echo "ℹ️ No se pudo eliminar la columna (quizás no existía).\n";
        }
    }
];

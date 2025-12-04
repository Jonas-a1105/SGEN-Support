<?php
/**
 * Migración: Agregar columna para rastrear notificaciones de vencimiento enviadas
 * Fecha: 2025-12-03
 */

return [
    'up' => function (PDO $pdo) {
        $checkCol = $pdo->query("SHOW COLUMNS FROM soportes LIKE 'notificacion_vencimiento_enviada'");
        if ($checkCol->rowCount() == 0) {
            $sql = "ALTER TABLE soportes 
                    ADD COLUMN notificacion_vencimiento_enviada BOOLEAN DEFAULT FALSE AFTER fecha_vencimiento,
                    ADD COLUMN notificacion_vencimiento_fecha DATETIME NULL AFTER notificacion_vencimiento_enviada";
            $pdo->exec($sql);
            echo "✓ Columnas de notificación agregadas a 'soportes'.\n";
        } else {
            echo "ℹ️ Las columnas de notificación ya existen.\n";
        }
    },

    'down' => function (PDO $pdo) {
        try {
            $pdo->exec("ALTER TABLE soportes 
                        DROP COLUMN notificacion_vencimiento_enviada,
                        DROP COLUMN notificacion_vencimiento_fecha");
            echo "✓ Columnas de notificación eliminadas.\n";
        } catch (Exception $e) {
            echo "ℹ️ No se pudieron eliminar las columnas (quizás no existían).\n";
        }
    }
];

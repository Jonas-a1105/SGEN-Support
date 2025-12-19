<?php
/**
 * Migración: Agregar campo jefe_area_nombre para entrada manual
 */

return [
    'up' => function (PDO $pdo) {
        $stmt = $pdo->query("SHOW COLUMNS FROM departamentos LIKE 'jefe_area_nombre'");
        if ($stmt->rowCount() > 0) {
            echo "La columna 'jefe_area_nombre' ya existe.\n";
        } else {
            $pdo->exec("ALTER TABLE departamentos ADD COLUMN jefe_area_nombre VARCHAR(200) NULL DEFAULT NULL");
            echo "Columna 'jefe_area_nombre' agregada exitosamente.\n";
        }
    },

    'down' => function (PDO $pdo) {
        $pdo->exec("ALTER TABLE departamentos DROP COLUMN jefe_area_nombre");
        echo "Columna 'jefe_area_nombre' eliminada.\n";
    }
];

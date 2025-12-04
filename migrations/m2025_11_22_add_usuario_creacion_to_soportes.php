<?php
// migrations/m2025_11_22_add_usuario_creacion_to_soportes.php

return [
    'up' => function (PDO $pdo) {
        // Agregar columna usuario_creacion_id a la tabla soportes
        $sql = "ALTER TABLE soportes ADD COLUMN usuario_creacion_id INT NULL AFTER empleado_id";
        $pdo->exec($sql);

        // Agregar foreign key
        $sql = "ALTER TABLE soportes ADD CONSTRAINT fk_soportes_usuario_creacion 
                FOREIGN KEY (usuario_creacion_id) REFERENCES usuarios(id) ON DELETE SET NULL";
        $pdo->exec($sql);
        
        echo "Migración aplicada: usuario_creacion_id agregado a soportes.\n";
    },
    'down' => function (PDO $pdo) {
        $sql = "ALTER TABLE soportes DROP FOREIGN KEY fk_soportes_usuario_creacion";
        $pdo->exec($sql);
        
        $sql = "ALTER TABLE soportes DROP COLUMN usuario_creacion_id";
        $pdo->exec($sql);
        
        echo "Migración revertida: usuario_creacion_id eliminado de soportes.\n";
    }
];

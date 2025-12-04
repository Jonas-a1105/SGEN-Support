<?php
/**
 * Migración: Crear tabla ticket_archivos
 * Fecha: 2025-12-03
 */

return [
    'up' => function (PDO $pdo) {
        $sql = "CREATE TABLE IF NOT EXISTS ticket_archivos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ticket_id INT NOT NULL,
            nombre_archivo VARCHAR(255) NOT NULL,
            nombre_original VARCHAR(255) NOT NULL,
            ruta VARCHAR(500) NOT NULL,
            tipo_mime VARCHAR(100),
            tamaño_bytes INT,
            subido_por INT,
            fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES soportes(id) ON DELETE CASCADE,
            FOREIGN KEY (subido_por) REFERENCES usuarios(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "✓ Tabla 'ticket_archivos' creada.\n";
    },

    'down' => function (PDO $pdo) {
        $pdo->exec("DROP TABLE IF EXISTS ticket_archivos");
        echo "✓ Tabla 'ticket_archivos' eliminada.\n";
    }
];

<?php
/**
 * Migración: Crear tabla ticket_comentarios
 * Fecha: 2025-12-03
 */

return [
    'up' => function (PDO $pdo) {
        $sql = "CREATE TABLE IF NOT EXISTS ticket_comentarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ticket_id INT NOT NULL,
            usuario_id INT NOT NULL,
            comentario TEXT NOT NULL,
            es_interno BOOLEAN DEFAULT FALSE,
            fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES soportes(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "✓ Tabla 'ticket_comentarios' creada.\n";
    },

    'down' => function (PDO $pdo) {
        $pdo->exec("DROP TABLE IF EXISTS ticket_comentarios");
        echo "✓ Tabla 'ticket_comentarios' eliminada.\n";
    }
];

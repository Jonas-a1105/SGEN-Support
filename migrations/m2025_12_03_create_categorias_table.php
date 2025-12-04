<?php
/**
 * Migración: Crear tabla de categorías y relacionar con soportes
 * Fecha: 2025-12-03
 */

return [
    'up' => function (PDO $pdo) {
        // 1. Crear tabla categorias
        $sql = "CREATE TABLE IF NOT EXISTS categorias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            icono VARCHAR(50) DEFAULT 'bi-tools',
            color VARCHAR(20) DEFAULT '#6c757d',
            activo BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($sql);
        echo "✓ Tabla 'categorias' creada.\n";

        // 2. Insertar categorías por defecto
        $categorias = [
            ['Hardware', 'Problemas físicos de equipos', 'bi-cpu', '#dc3545'], // Rojo
            ['Software', 'Errores de programas y SO', 'bi-window', '#0d6efd'], // Azul
            ['Red', 'Conectividad e internet', 'bi-wifi', '#198754'], // Verde
            ['Impresora', 'Problemas de impresión', 'bi-printer', '#ffc107'], // Amarillo
            ['Periféricos', 'Mouse, teclado, monitor', 'bi-mouse', '#6c757d'], // Gris
            ['Otro', 'Otros problemas', 'bi-question-circle', '#6c757d']
        ];

        $stmt = $pdo->prepare("INSERT INTO categorias (nombre, descripcion, icono, color) VALUES (?, ?, ?, ?)");
        foreach ($categorias as $cat) {
            // Verificar si ya existe para no duplicar
            $check = $pdo->prepare("SELECT id FROM categorias WHERE nombre = ?");
            $check->execute([$cat[0]]);
            if (!$check->fetch()) {
                $stmt->execute($cat);
            }
        }
        echo "✓ Categorías por defecto insertadas.\n";

        // 3. Agregar columna categoria_id a soportes
        $checkCol = $pdo->query("SHOW COLUMNS FROM soportes LIKE 'categoria_id'");
        if ($checkCol->rowCount() == 0) {
            $sql = "ALTER TABLE soportes 
                    ADD COLUMN categoria_id INT NULL AFTER equipo_id,
                    ADD CONSTRAINT fk_soporte_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL";
            $pdo->exec($sql);
            echo "✓ Columna 'categoria_id' agregada a 'soportes'.\n";
        }
    },

    'down' => function (PDO $pdo) {
        // Eliminar FK y columna
        try {
            $pdo->exec("ALTER TABLE soportes DROP FOREIGN KEY fk_soporte_categoria");
        } catch (Exception $e) {} // Ignorar si no existe
        
        try {
            $pdo->exec("ALTER TABLE soportes DROP COLUMN categoria_id");
        } catch (Exception $e) {}

        // Eliminar tabla
        $pdo->exec("DROP TABLE IF EXISTS categorias");
        
        echo "Migración revertida: tabla 'categorias' eliminada y columna removida de soportes.\n";
    }
];

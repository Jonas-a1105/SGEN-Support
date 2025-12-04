<?php
// Migration to create bajas_inventario table
require __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $sql = "
    CREATE TABLE IF NOT EXISTS bajas_inventario (
        id INT AUTO_INCREMENT PRIMARY KEY,
        inventario_id INT NOT NULL,
        cantidad INT NOT NULL,
        motivo TEXT,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        usuario_id INT,
        FOREIGN KEY (inventario_id) REFERENCES inventario_items(id) ON DELETE CASCADE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sql);
    echo "Migration 'm2025_11_23_bajas_inventario' executed successfully.\n";
} catch (PDOException $e) {
    echo "Error executing migration: " . $e->getMessage() . "\n";
}

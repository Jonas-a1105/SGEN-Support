<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Add new columns to inventario_items
    $sql = "ALTER TABLE inventario_items 
            ADD COLUMN fecha_compra DATE NULL,
            ADD COLUMN proveedor VARCHAR(255) NULL,
            ADD COLUMN proveedor_rif VARCHAR(50) NULL,
            ADD COLUMN garantia_fin DATE NULL,
            ADD COLUMN valor_compra DECIMAL(10,2) DEFAULT 0.00";

    $pdo->exec($sql);
    echo "Columns added successfully to inventario_items table.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

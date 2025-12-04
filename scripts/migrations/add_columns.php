<?php
require_once 'src/Core/Database.php';
use App\Core\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    
    $sql = "ALTER TABLE inventario_items 
            ADD COLUMN marca VARCHAR(100) NULL AFTER descripcion,
            ADD COLUMN modelo VARCHAR(100) NULL AFTER marca,
            ADD COLUMN unidad_medida VARCHAR(50) NULL AFTER modelo";
            
    $pdo->exec($sql);
    echo "Columns added successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

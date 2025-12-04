<?php
require_once 'src/Core/Database.php';
use App\Core\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->query("DESCRIBE inventario_items");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(", ", $columns);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

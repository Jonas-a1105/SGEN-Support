<?php
require_once 'src/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

$stmt = $conn->query("DESCRIBE equipos");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    if ($col['Field'] === 'estado') {
        echo "Column: {$col['Field']}\nType: {$col['Type']}\n";
    }
}

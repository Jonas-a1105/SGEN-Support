<?php
require_once __DIR__ . '/../src/Core/Database.php';

use App\Core\Database;

$pdo = Database::getInstance()->getConnection();

$sql = "SELECT id, nombre, cedula FROM empleados LIMIT 10";
$stmt = $pdo->query($sql);
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "--- START EMPLEADOS ---\n";
print_r($empleados);
echo "--- END EMPLEADOS ---\n";

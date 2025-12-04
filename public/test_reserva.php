<?php
require_once 'src/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

try {
    $sql = "INSERT INTO equipos (codigo_inventario, tipo, marca, modelo, numero_serie, estado) VALUES ('TEST-RES-001', 'computadora', 'TestBrand', 'TestModel', 'SN123456', 'en_reserva')";
    $conn->exec($sql);
    echo "Successfully inserted equipment with status 'en_reserva'.\n";
    
    // Clean up
    $conn->exec("DELETE FROM equipos WHERE codigo_inventario = 'TEST-RES-001'");
    echo "Test data cleaned up.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

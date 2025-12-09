<?php
require_once 'src/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

try {
    $sql = "ALTER TABLE equipos MODIFY COLUMN estado ENUM('nuevo','usado','en_uso','fuera_de_servicio','en_reparacion','disponible','en_reserva') DEFAULT 'nuevo'";
    $conn->exec($sql);
    echo "Successfully added 'en_reserva' to estado ENUM.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

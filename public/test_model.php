<?php
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Core/Model.php';
require_once __DIR__ . '/../src/Models/Empleado.php';

use App\Models\Empleado;

$empleadoModel = new Empleado();
$cedula = '32-076-356';
$empleado = $empleadoModel->findByCedula($cedula);

echo "--- SEARCH RESULT FOR '$cedula' ---\n";
if ($empleado) {
    echo "Found: " . json_encode($empleado) . "\n";
} else {
    echo "Not Found\n";
}

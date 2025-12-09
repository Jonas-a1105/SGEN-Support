<?php
// Script de prueba para verificar el estado en la base de datos
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Equipo;

try {
    $equipoModel = new Equipo();
    
    // Obtener los últimos 5 equipos sin asignar
    $equipos = $equipoModel->findUnassigned();
    
    echo "<h2>Equipos sin asignar (últimos registrados):</h2>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Código</th><th>Serial</th><th>Marca</th><th>Modelo</th><th>ESTADO</th><th>Created At</th></tr>";
    
    foreach ($equipos as $equipo) {
        echo "<tr>";
        echo "<td>{$equipo->id}</td>";
        echo "<td>{$equipo->codigo_inventario}</td>";
        echo "<td>{$equipo->numero_serie}</td>";
        echo "<td>{$equipo->marca}</td>";
        echo "<td>{$equipo->modelo}</td>";
        echo "<td><strong style='color: red;'>" . ($equipo->estado ?? 'NULL/VACÍO') . "</strong></td>";
        echo "<td>{$equipo->created_at}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h3>Datos en bruto (para debug):</h3>";
    echo "<pre>";
    print_r($equipos);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

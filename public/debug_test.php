<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Equipo;

$equipoModel = new Equipo();
$equipos = $equipoModel->findUnassigned();

echo "<pre>";
print_r($equipos);
echo "</pre>";

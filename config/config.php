<?php
// Configuración de la URL BASE Automática
// Esto detecta si es HTTP o HTTPS y construye la URL correcta
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
// Detecta la carpeta del proyecto (quita /public/index.php)
$path = str_replace('/public/index.php', '', $_SERVER['SCRIPT_NAME']); 

// Define la constante BASE_URL automáticamente
define('BASE_URL', $protocol . '://' . $host . $path . '/');

// Configuración de Base de Datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'sgen_db'); // Asegúrate que este nombre sea exacto en Linux
define('DB_USER', 'root');
define('DB_PASS', ''); // Cambia esto según tu servidor Linux
define('DB_CHARSET', 'utf8mb4');
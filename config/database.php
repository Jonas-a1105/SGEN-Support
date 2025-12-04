<?php
// Configuración de Base de Datos
// Definimos las constantes SOLO si no existen, por seguridad.

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'sgen_db');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', ''); // Pon tu contraseña aquí si tienes
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');
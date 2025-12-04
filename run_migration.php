<?php
require 'config/database.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $migration = require 'migrations/m2025_12_03_add_notificacion_vencimiento.php';
    $migration['up']($pdo);
    
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

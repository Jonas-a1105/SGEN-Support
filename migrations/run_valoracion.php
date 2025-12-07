<?php
/**
 * Script para ejecutar la migración de valoración
 */

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conectado a la base de datos.\n";
    
    // Cargar y ejecutar migración
    $migration = require __DIR__ . '/m2025_12_05_add_valoracion_to_soportes.php';
    $migration['up']($pdo);
    
    echo "\n=== Migración completada ===\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}

<?php
// migrations/m2025_11_25_change_equipos_tipo_varchar.php
require __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Modificando columna 'tipo' en tabla 'equipos' a VARCHAR(100)...\n";

    // Alter table to modify the column type
    // We use MODIFY COLUMN to change the definition
    $sql = "ALTER TABLE equipos 
            MODIFY COLUMN tipo VARCHAR(100) NOT NULL";
    
    $pdo->exec($sql);
    echo "Migración exitosa: Columna 'tipo' actualizada a VARCHAR(100).\n";

} catch (PDOException $e) {
    echo "Error ejecutando migración: " . $e->getMessage() . "\n";
}

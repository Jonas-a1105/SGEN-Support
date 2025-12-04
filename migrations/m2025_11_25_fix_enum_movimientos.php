<?php
// migrations/m2025_11_25_fix_enum_movimientos.php
require __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Modificando columna tipo_movimiento en inventario_movimientos...\n";

    // Alter table to modify the ENUM column to include new values
    $sql = "ALTER TABLE inventario_movimientos 
            MODIFY COLUMN tipo_movimiento 
            ENUM('ENTRADA', 'SALIDA', 'AJUSTE', 'BAJA', 'CONSUMO', 'TRANSFERENCIA') 
            NOT NULL";
    
    $pdo->exec($sql);
    echo "Migración exitosa: Columna actualizada correctamente.\n";

} catch (PDOException $e) {
    echo "Error ejecutando migración: " . $e->getMessage() . "\n";
}

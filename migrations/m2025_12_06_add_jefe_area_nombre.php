<?php
/**
 * Migración: Agregar campo jefe_area_nombre para entrada manual
 */
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->query("SHOW COLUMNS FROM departamentos LIKE 'jefe_area_nombre'");
    if ($stmt->rowCount() > 0) {
        echo "La columna 'jefe_area_nombre' ya existe.\n";
    } else {
        $pdo->exec("ALTER TABLE departamentos ADD COLUMN jefe_area_nombre VARCHAR(200) NULL DEFAULT NULL");
        echo "Columna 'jefe_area_nombre' agregada exitosamente.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

<?php
/**
 * Migración: Agregar campo jefe_area_id a departamentos
 * Este campo es opcional y referencia a un empleado como jefe del área
 */

// Cargar configuración
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Conectado a la base de datos.\n";

    // Verificar si la columna ya existe
    $stmt = $pdo->query("SHOW COLUMNS FROM departamentos LIKE 'jefe_area_id'");
    if ($stmt->rowCount() > 0) {
        echo "La columna 'jefe_area_id' ya existe en la tabla 'departamentos'.\n";
    } else {
        // Agregar columna jefe_area_id (opcional, referencia a empleados)
        $sql = "ALTER TABLE departamentos 
                ADD COLUMN jefe_area_id INT NULL DEFAULT NULL,
                ADD COLUMN descripcion VARCHAR(500) NULL DEFAULT NULL";
        
        $pdo->exec($sql);
        echo "Columnas 'jefe_area_id' y 'descripcion' agregadas exitosamente.\n";

        // Agregar índice opcional (no foreign key para flexibilidad)
        $pdo->exec("ALTER TABLE departamentos ADD INDEX idx_jefe_area (jefe_area_id)");
        echo "Índice agregado.\n";
    }

    echo "\n=== Migración completada exitosamente ===\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

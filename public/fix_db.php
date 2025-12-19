<?php
// Temporary script to fix missing DB column
// Run by accessing /fix_db.php in the browser

require_once __DIR__ . '/../config/database.php';

try {
    // Create connection manually to verify it works
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h1>Database Repair Tool</h1>";
    echo "<p>Connected to database on port " . DB_PORT . "</p>";

    // Code from m2025_12_06_add_jefe_area_to_departamentos.php
    
    echo "<h2>Checking 'departamentos' table...</h2>";
    
    // Verificar si la columna ya existe
    $stmt = $pdo->query("SHOW COLUMNS FROM departamentos LIKE 'jefe_area_id'");
    if ($stmt->rowCount() > 0) {
        echo "<div style='color: green'>✓ Column 'jefe_area_id' already exists.</div>";
    } else {
        echo "<div>Adding 'jefe_area_id'...</div>";
        // Agregar columna jefe_area_id (opcional, referencia a empleados)
        $sql = "ALTER TABLE departamentos 
                ADD COLUMN jefe_area_id INT NULL DEFAULT NULL,
                ADD COLUMN descripcion VARCHAR(500) NULL DEFAULT NULL";
        
        $pdo->exec($sql);
        echo "<div style='color: green'>✓ Columns added successfully.</div>";

        // Agregar índice opcional
        try {
            $pdo->exec("ALTER TABLE departamentos ADD INDEX idx_jefe_area (jefe_area_id)");
            echo "<div style='color: green'>✓ Index added.</div>";
        } catch (Exception $e) {
             echo "<div style='color: orange'>⚠ Index might already exist: " . $e->getMessage() . "</div>";
        }
    }
    
    echo "<h2>Done!</h2>";
    echo "<p>You can now close this window and try the action again.</p>";

} catch (PDOException $e) {
    echo "<h1 style='color: red'>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

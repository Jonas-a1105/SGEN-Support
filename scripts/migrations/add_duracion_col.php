<?php
require_once __DIR__ . '/../../src/Core/Database.php';

use App\Core\Database;

try {
    // Correct Singleton usage
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Check if column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM mantenimientos LIKE 'duracion'");
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        echo "Adding 'duracion' column to 'mantenimientos' table...\n";
        // Duration in minutes
        $sql = "ALTER TABLE mantenimientos ADD COLUMN duracion INT DEFAULT 60 COMMENT 'Duration in minutes' AFTER fecha";
        $pdo->exec($sql);
        echo "Column 'duracion' added successfully.\n";
    } else {
        echo "Column 'duracion' already exists.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

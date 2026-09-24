<?php
/**
 * Migration: Enterprise Enhancements
 * Adds IP/Machine logging to Bitacora and versioning to Soportes (tickets).
 */

return [
    'name' => 'Enterprise Update',
    'up' => function ($db) {
        // 1. Update bitacora_acciones
        echo "Updating bitacora_acciones...\n";
        $db->exec("ALTER TABLE bitacora_acciones ADD COLUMN IF NOT EXISTS ip_address VARCHAR(45) DEFAULT NULL AFTER username");
        $db->exec("ALTER TABLE bitacora_acciones ADD COLUMN IF NOT EXISTS machine_name VARCHAR(100) DEFAULT NULL AFTER ip_address");
        echo "Bitacora table updated.\n";

        // 2. Update soportes (for optimistic locking)
        echo "Updating soportes table...\n";
        $db->exec("ALTER TABLE soportes ADD COLUMN IF NOT EXISTS version_id INT DEFAULT 1");
        echo "Soportes table updated.\n";

        echo "Migration completed successfully.\n";
    }
];

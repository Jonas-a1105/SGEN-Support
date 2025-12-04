<?php
use App\Core\Database;

class m2025_12_03_add_firma_to_soportes {
    public function up() {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        $sql = "ALTER TABLE soportes ADD COLUMN firma_usuario LONGTEXT DEFAULT NULL";
        $conn->exec($sql);
    }

    public function down() {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        $sql = "ALTER TABLE soportes DROP COLUMN firma_usuario";
        $conn->exec($sql);
    }
}

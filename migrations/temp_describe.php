<?php
require_once __DIR__ . '/../config/database.php';

$pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
    DB_USER, DB_PASS
);

$stmt = $pdo->query('DESCRIBE soportes');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    echo $col['Field'] . " - " . $col['Type'] . "\n";
}

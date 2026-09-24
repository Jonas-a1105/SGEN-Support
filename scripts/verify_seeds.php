<?php
/**
 * Verify seeds.php content
 */

$seeds = include __DIR__ . '/../database/seeds.php';

echo "=== Contenido de seeds.php ===\n\n";

foreach ($seeds as $table => $records) {
    echo sprintf("%-30s: %d registros\n", $table, count($records));
}

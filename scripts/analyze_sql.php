<?php
/**
 * Analyze sgen_db.sql to count records per table
 */

echo "=== Analyzing sgen_db.sql ===\n\n";

// Read file
$content = file_get_contents(__DIR__ . '/../database/sgen_db.sql');

// Check if UTF-16 LE and convert
if (substr($content, 0, 2) === "\xFF\xFE") {
    $content = substr($content, 2);
    $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
    echo "File encoding: UTF-16 LE (converted to UTF-8)\n\n";
}

// Find all INSERT statements
preg_match_all('/INSERT INTO `([^`]+)` VALUES\s*\n(.*?);/s', $content, $matches, PREG_SET_ORDER);

echo "Tables with INSERT statements:\n";
echo str_repeat('-', 50) . "\n";

$totalRecords = 0;
foreach ($matches as $match) {
    $tableName = $match[1];
    $valuesBlock = $match[2];
    
    // Count records by counting opening parentheses at start of value tuples
    // Each record starts with ( after a newline or comma
    $recordCount = preg_match_all('/\([0-9]+,/', $valuesBlock);
    
    // Alternative: count lines with data
    $lines = explode("\n", $valuesBlock);
    $dataLines = 0;
    foreach ($lines as $line) {
        if (preg_match('/^\s*\(/', trim($line))) {
            $dataLines++;
        }
    }
    
    echo sprintf("%-35s: %d records\n", $tableName, $dataLines);
    $totalRecords += $dataLines;
}

echo str_repeat('-', 50) . "\n";
echo "Total records in file: $totalRecords\n";

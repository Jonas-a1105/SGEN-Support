<?php
/**
 * Convert sgen_db.sql from UTF-16 LE to UTF-8 and import to database
 */

echo "Converting sgen_db.sql to UTF-8...\n";

$inputFile = __DIR__ . '/../database/sgen_db.sql';
$outputFile = __DIR__ . '/../database/sgen_db_clean.sql';

// Read UTF-16 LE file
$content = file_get_contents($inputFile);

// Remove BOM if present
if (substr($content, 0, 2) === "\xFF\xFE") {
    $content = substr($content, 2);
}

// Convert from UTF-16 LE to UTF-8
$content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');

// Write clean UTF-8 file
file_put_contents($outputFile, $content);

echo "Conversion complete. File saved to: $outputFile\n";
echo "File size: " . filesize($outputFile) . " bytes\n";

<?php
// Script de depuración para verificar si las firmas se están guardando
require 'config/database.php';

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
$pdo = new PDO($dsn, DB_USER, DB_PASS);

$sql = "SELECT id, estado, firma_usuario FROM soportes WHERE firma_usuario IS NOT NULL AND firma_usuario != '' LIMIT 10";
$stmt = $pdo->query($sql);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== TICKETS CON FIRMA ===\n\n";

if (empty($tickets)) {
    echo "❌ No hay tickets con firma guardada.\n";
} else {
    foreach ($tickets as $ticket) {
        echo "Ticket #{$ticket['id']} ({$ticket['estado']})\n";
        echo "Firma: " . substr($ticket['firma_usuario'], 0, 50) . "...\n";
        echo "Longitud: " . strlen($ticket['firma_usuario']) . " caracteres\n\n";
    }
}

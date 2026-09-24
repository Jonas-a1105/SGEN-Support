<?php
/**
 * Concurrency Test Script for SGEN Support
 * Simulates multiple technicians attempting to take the same ticket.
 */

// Define project root
define('PROJECT_ROOT', dirname(__DIR__));

// Load environment and config
require_once PROJECT_ROOT . '/config/database.php';
require_once PROJECT_ROOT . '/src/Core/Database.php';

use App\Core\Database;

function simulateTakeTicket($tecnicoId, $ticketId, $delaySeconds = 2) {
    echo "\n[Técnico $tecnicoId] Iniciando intento para ticket #$ticketId...\n";
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Start Transaction
        $db->beginTransaction();
        echo "[Técnico $tecnicoId] Transacción iniciada.\n";

        // SELECT WITH FOR UPDATE (Crucial for atomic locking)
        echo "[Técnico $tecnicoId] Ejecutando SELECT FOR UPDATE (Bloqueando fila)...\n";
        $stmt = $db->prepare("SELECT estado FROM tickets WHERE id = ? FOR UPDATE");
        $stmt->execute([$ticketId]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) {
            echo "[Técnico $tecnicoId] Error: Ticket no encontrado.\n";
            $db->rollBack();
            return;
        }

        echo "[Técnico $tecnicoId] Estado actual: " . $ticket['estado'] . "\n";

        if ($ticket['estado'] === 'abierto') {
            echo "[Técnico $tecnicoId] Ticket disponible. Simulando pausa de procesamiento de $delaySeconds segundos...\n";
            sleep($delaySeconds); // This mimics the split-second gap where a race condition usually happens

            $update = $db->prepare("UPDATE tickets SET estado = 'en_proceso', tecnico_id = ?, updated_at = NOW() WHERE id = ?");
            $update->execute([$tecnicoId, $ticketId]);
            
            $db->commit();
            echo "[Técnico $tecnicoId] EXITO: Ticket #$ticketId asignado correctamente.\n";
        } else {
            echo "[Técnico $tecnicoId] FALLO: El ticket ya no está disponible (Estado: " . $ticket['estado'] . ").\n";
            $db->rollBack();
        }
    } catch (Exception $e) {
        if (isset($db)) $db->rollBack();
        echo "[Técnico $tecnicoId] ERROR FATAL: " . $e->getMessage() . "\n";
    }
}

// Check arguments
if ($argc < 3) {
    echo "Uso: php concurrency_test.php <ID_TECNICO> <ID_TICKET>\n";
    echo "Ejemplo para simular concurrencia:\n";
    echo "  Abra dos terminales y ejecute casi al mismo tiempo:\n";
    echo "  Terminal 1: php concurrency_test.php 5 101\n";
    echo "  Terminal 2: php concurrency_test.php 9 101\n";
    exit(1);
}

$tecnicoId = $argv[1];
$ticketId = $argv[2];

simulateTakeTicket($tecnicoId, $ticketId);

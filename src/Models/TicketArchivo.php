<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class TicketArchivo extends Model
{
    protected $table = 'ticket_archivos';

    public $id;
    public $ticket_id;
    public $nombre_archivo;
    public $nombre_original;
    public $ruta;
    public $tipo_mime;
    public $tamaño_bytes;
    public $subido_por;
    public $fecha_subida;

    /**
     * Obtener archivos de un ticket
     */
    public function findByTicketId($ticketId)
    {
        $sql = "SELECT ta.*, u.username as nombre_usuario 
                FROM {$this->table} ta
                LEFT JOIN usuarios u ON ta.subido_por = u.id
                WHERE ta.ticket_id = ?
                ORDER BY ta.fecha_subida DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}

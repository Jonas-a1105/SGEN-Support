<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class TicketComentario extends Model
{
    protected $table = 'ticket_comentarios';

    public $id;
    public $ticket_id;
    public $usuario_id;
    public $comentario;
    public $es_interno;
    public $fecha;

    public function create(array $data)
    {
        $sql = "INSERT INTO {$this->table} (ticket_id, usuario_id, comentario, es_interno, fecha) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        
        if ($stmt->execute([
            $data['ticket_id'],
            $data['usuario_id'],
            $data['comentario'],
            $data['es_interno'] ?? 0
        ])) {
            return (int) $this->pdo->lastInsertId();
        }

        return false;
    }

    public function findByTicketId(int $ticketId, bool $includeInternos = false)
    {
        $sql = "
            SELECT 
                tc.*, 
                u.username AS nombre_usuario,
                u.rol AS rol_usuario
            FROM {$this->table} tc
            JOIN usuarios u ON tc.usuario_id = u.id
            WHERE tc.ticket_id = ?
        ";

        if (!$includeInternos) {
            $sql .= " AND tc.es_interno = 0";
        }

        $sql .= " ORDER BY tc.fecha ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$ticketId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}

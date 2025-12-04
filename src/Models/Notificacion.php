<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    /**
     * Crea una nueva notificación para un usuario.
     *
     * @param int $usuario_id A quién notificar
     * @param string $mensaje El texto a mostrar
     * @param string $enlace A dónde ir al hacer clic (ej: /soportes/ver/10)
     * @param string $tipo Tipo: info, success, warning, danger (default: info)
     * @return bool
     */
    public function createNotification(int $usuario_id, string $mensaje, string $enlace, string $tipo = 'info')
    {
        $sql = "INSERT INTO {$this->table} (usuario_id, mensaje, enlace) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$usuario_id, $mensaje, $enlace]);
    }

    /**
     * Busca todas las notificaciones (leídas y no leídas) para un usuario.
     *
     * @param int $usuario_id
     * @param int $limit
     * @return array
     */
    public function findByUsuario(int $usuario_id, int $limit = 10)
    {
        // Trae las últimas 10, no leídas primero
        $sql = "SELECT * FROM {$this->table} 
                WHERE usuario_id = ? 
                ORDER BY leido ASC, created_at DESC 
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $usuario_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Cuenta las notificaciones NO LEÍDAS de un usuario.
     *
     * @param int $usuario_id
     * @return int
     */
    public function countUnread(int $usuario_id): int
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE usuario_id = ? AND leido = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Marca una notificación específica como leída.
     *
     * @param int $notificacion_id
     * @param int $usuario_id (Por seguridad, para que un usuario no lea las de otro)
     * @return bool
     */
    public function markAsRead(int $notificacion_id, int $usuario_id): bool
    {
        $sql = "UPDATE {$this->table} SET leido = 1 
                WHERE id = ? AND usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$notificacion_id, $usuario_id]);
    }

    /**
     * Marca TODAS las notificaciones de un usuario como leídas.
     *
     * @param int $usuario_id
     * @return bool
     */
    public function markAllAsRead(int $usuario_id): bool
    {
        $sql = "UPDATE {$this->table} SET leido = 1 
                WHERE usuario_id = ? AND leido = 0";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$usuario_id]);
    }

    /**
     * Elimina notificaciones con enlaces rotos (que contienen {id} literal).
     *
     * @return int Número de notificaciones eliminadas
     */
    public function deleteBrokenLinks(): int
    {
        $sql = "DELETE FROM {$this->table} WHERE enlace LIKE '%{id}%' OR enlace LIKE '%{%}%'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }
    
    /**
     * Detecta el tipo de notificación basado en el mensaje
     */
    public function detectType(string $mensaje): string
    {
        $mensaje = strtolower($mensaje);
        
        if (strpos($mensaje, 'asignado') !== false || strpos($mensaje, 'nuevo ticket') !== false) {
            return 'info';
        } elseif (strpos($mensaje, 'resuelto') !== false || strpos($mensaje, 'completado') !== false) {
            return 'success';
        } elseif (strpos($mensaje, 'espera') !== false || strpos($mensaje, 'pendiente') !== false) {
            return 'warning';
        } elseif (strpos($mensaje, 'eliminado') !== false || strpos($mensaje, 'cancelado') !== false) {
            return 'danger';
        }
        
        return 'info';
    }
}
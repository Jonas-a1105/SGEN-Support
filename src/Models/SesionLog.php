<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class SesionLog extends Model
{
    protected $table = 'sesiones_log';

    public function createLog(int $usuario_id, string $username)
    {
        $sql = "INSERT INTO {$this->table} (usuario_id, username, fecha_inicio) 
                VALUES (?, ?, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        
        if ($stmt->execute([$usuario_id, $username])) {
            return (int) $this->pdo->lastInsertId();
        }
        return false;
    }

    public function updateLogoutTime(int $log_id): bool
    {
        $sql = "UPDATE {$this->table} SET fecha_fin = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$log_id]);
    }

    /**
     * (NUEVA FUNCIÓN)
     * Cierra todas las sesiones 'activas' (fecha_fin IS NULL)
     * de un usuario específico, excepto la sesión actual que se
     * está creando (opcional).
     *
     * @param int $usuario_id
     * @return bool
     */
    public function closeAllUserSessions(int $usuario_id): bool
    {
        $sql = "UPDATE {$this->table} 
                SET fecha_fin = NOW() 
                WHERE usuario_id = ? AND fecha_fin IS NULL";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$usuario_id]);
    }

    public function findLatestLogs(int $limit = 200): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY fecha_inicio DESC LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findByUsuarioId(int $usuarioId, int $limit = 200): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE usuario_id = :uid ORDER BY fecha_inicio DESC LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':uid', $usuarioId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    public function findByDepartmentId(int $departamentoId, int $limit = 200): array
    {
        $sql = "
            SELECT sl.*, u.username 
            FROM {$this->table} sl
            JOIN empleados e ON sl.usuario_id = e.usuario_id
            JOIN usuarios u ON sl.usuario_id = u.id
            WHERE e.departamento_id = ?
            ORDER BY sl.fecha_inicio DESC
            LIMIT ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $departamentoId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
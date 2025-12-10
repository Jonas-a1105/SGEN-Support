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

    /**
     * Finds logs with dynamic filters and pagination.
     */
    public function findAllPaginated(int $limit, int $offset, array $filters = []): array
    {
        $sql = "SELECT sl.* FROM {$this->table} sl 
                LEFT JOIN usuarios u ON sl.usuario_id = u.id
                WHERE 1=1";
        $params = [];

        // Filter by username
        if (!empty($filters['username'])) {
            $sql .= " AND sl.username LIKE :username";
            $params[':username'] = '%' . $filters['username'] . '%';
        }

        // Filter by user_id (exact match)
        if (!empty($filters['usuario_id'])) {
            $sql .= " AND sl.usuario_id = :usuario_id";
            $params[':usuario_id'] = $filters['usuario_id'];
        }

        // Filter by date range
        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND DATE(sl.fecha_inicio) >= :fecha_desde";
            $params[':fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND DATE(sl.fecha_inicio) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filters['fecha_hasta'];
        }

        $sql .= " ORDER BY sl.fecha_inicio DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Counts logs with dynamic filters.
     */
    public function countAll(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} sl 
                LEFT JOIN usuarios u ON sl.usuario_id = u.id
                WHERE 1=1";
        $params = [];

        // Filter by username
        if (!empty($filters['username'])) {
            $sql .= " AND sl.username LIKE :username";
            $params[':username'] = '%' . $filters['username'] . '%';
        }

        // Filter by user_id (exact match)
        if (!empty($filters['usuario_id'])) {
            $sql .= " AND sl.usuario_id = :usuario_id";
            $params[':usuario_id'] = $filters['usuario_id'];
        }

        // Filter by date range
        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND DATE(sl.fecha_inicio) >= :fecha_desde";
            $params[':fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND DATE(sl.fecha_inicio) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filters['fecha_hasta'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        return $result ? (int)$result->total : 0;
    }
}
<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Bitacora extends Model
{
    protected $table = 'bitacora_acciones';

    /**
     * Crea un nuevo registro en la bitácora.
     * (Función actualizada para aceptar enlaces)
     *
     * @param int $usuario_id
     * @param string $username
     * @param string $accion
     * @param string|null $enlace_tipo
     * @param int|null $enlace_id
     * @return bool
     */
    public function createLog(int $usuario_id, string $username, string $accion, ?string $enlace_tipo = null, ?int $enlace_id = null)
    {
        $sql = "INSERT INTO {$this->table} (usuario_id, username, accion, enlace_tipo, enlace_id) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$usuario_id, $username, $accion, $enlace_tipo, $enlace_id]);
    }

    /**
     * Obtiene los últimos N registros de la bitácora.
     * @param int $limit
     * @return array
     */
    public function findAllSorted(int $limit = 200): array
    {
        $sql = "SELECT * FROM {$this->table} 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Cuenta el total de registros en la bitácora.
     * @return int
     */
    public function countAll(): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? (int)$result->total : 0;
    }

    /**
     * Obtiene registros paginados de la bitácora.
     * @param int $limit  Registros por página
     * @param int $offset Desplazamiento
     * @return array
     */
    public function findPaginated(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findByDepartmentId(int $departamentoId, int $limit = 50, int $offset = 0)
    {
        $sql = "
            SELECT b.*, u.username 
            FROM {$this->table} b
            JOIN usuarios u ON b.usuario_id = u.id
            JOIN empleados e ON u.id = e.usuario_id
            WHERE e.departamento_id = ?
            ORDER BY b.created_at DESC
            LIMIT ? OFFSET ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $departamentoId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function countByDepartmentId(int $departamentoId)
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM {$this->table} b
            JOIN usuarios u ON b.usuario_id = u.id
            JOIN empleados e ON u.id = e.usuario_id
            WHERE e.departamento_id = ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$departamentoId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? (int)$result->total : 0;
    }

    /**
     * Obtiene registros de bitácora para una entidad específica.
     * @param string $enlaceTipo Tipo de entidad (ej: 'equipo', 'soporte')
     * @param int $enlaceId ID de la entidad
     * @return array
     */
    public function findByEntity(string $enlaceTipo, int $enlaceId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE enlace_tipo = ? AND enlace_id = ?
                ORDER BY created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$enlaceTipo, $enlaceId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
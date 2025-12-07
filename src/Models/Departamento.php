<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Departamento extends Model
{
    protected $table = 'departamentos';

    public function findByName(string $nombre)
    {
        $sql = "SELECT * FROM {$this->table} WHERE nombre = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nombre]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function countAll()
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? $result->total : 0;
    }

    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Obtiene todos los departamentos con estadísticas de empleados y equipos
     */
    public function findAllWithStats()
    {
        $sql = "SELECT d.*, 
                       (SELECT COUNT(*) FROM empleados e WHERE e.departamento_id = d.id) as empleados_count,
                       (SELECT COUNT(*) FROM equipos eq WHERE eq.departamento_id = d.id) as equipos_count,
                       COALESCE(
                           d.jefe_area_nombre,
                           CONCAT(jefe.nombre, ' ', IFNULL(jefe.apellido, ''))
                       ) as jefe_nombre
                FROM {$this->table} d 
                LEFT JOIN empleados jefe ON d.jefe_area_id = jefe.id
                ORDER BY d.nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    public function findByIdWithStats($id)
    {
        $sql = "SELECT d.*, 
                       (SELECT COUNT(*) FROM empleados e WHERE e.departamento_id = d.id) as empleados_count,
                       (SELECT COUNT(*) FROM equipos eq WHERE eq.departamento_id = d.id) as equipos_count,
                       COALESCE(
                           d.jefe_area_nombre,
                           CONCAT(jefe.nombre, ' ', IFNULL(jefe.apellido, ''))
                       ) as jefe_nombre
                FROM {$this->table} d 
                LEFT JOIN empleados jefe ON d.jefe_area_id = jefe.id
                WHERE d.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
}
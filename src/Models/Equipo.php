<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Equipo extends Model
{
    protected $table = 'equipos';

    public function findAllWithDetails()
    {
        $sql = "
            SELECT 
                e.*, 
                d.nombre AS departamento_nombre,
                emp.nombre AS empleado_nombre,
                emp.apellido AS empleado_apellido
            FROM {$this->table} e
            LEFT JOIN departamentos d ON e.departamento_id = d.id
            LEFT JOIN empleados emp ON e.empleado_id = emp.id
            ORDER BY e.id DESC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findById($id)
    {
        $sql = "
            SELECT 
                e.*, 
                d.nombre AS departamento_nombre,
                CONCAT(emp.nombre, ' ', emp.apellido) AS empleado_nombre
            FROM {$this->table} e
            LEFT JOIN departamentos d ON e.departamento_id = d.id
            LEFT JOIN empleados emp ON e.empleado_id = emp.id
            WHERE e.id = ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function findBySerial(string $serial)
    {
        $sql = "SELECT * FROM {$this->table} WHERE numero_serie = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$serial]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function findByCodigo(string $codigo)
    {
        $sql = "SELECT * FROM {$this->table} WHERE codigo_inventario = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$codigo]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function countAll()
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? $result->total : 0;
    }

    public function countByEstado(string $estado)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE estado = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$estado]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? $result->total : 0;
    }

    public function getMantenimientos($equipo_id)
    {
        $sql = "SELECT * FROM mantenimientos WHERE equipo_id = ? ORDER BY fecha DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$equipo_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findByDepartamentoId($departamento_id)
    {
        $sql = "SELECT e.*, d.nombre AS departamento_nombre,
                       CONCAT(emp.nombre, ' ', IFNULL(emp.apellido, '')) AS empleado_nombre
                FROM {$this->table} e 
                LEFT JOIN departamentos d ON e.departamento_id = d.id 
                LEFT JOIN empleados emp ON e.empleado_id = emp.id
                WHERE e.departamento_id = ? 
                ORDER BY e.codigo_inventario ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$departamento_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findByEmpleadoId($empleado_id)
    {
        $sql = "SELECT e.*, d.nombre AS departamento_nombre 
                FROM {$this->table} e 
                LEFT JOIN departamentos d ON e.departamento_id = d.id 
                WHERE e.empleado_id = ? 
                ORDER BY e.codigo_inventario ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$empleado_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function countByDepartamento(int $departamentoId)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE departamento_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$departamentoId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? $result->total : 0;
    }

    public function countByEstadoAndDepartamento(string $estado, int $departamentoId)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE estado = ? AND departamento_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$estado, $departamentoId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? $result->total : 0;
    }

    public function findUnassigned()
    {
        $sql = "SELECT * FROM {$this->table} WHERE departamento_id IS NULL AND empleado_id IS NULL ORDER BY id DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findUnassignedPaginated(int $limit, int $offset, string $search = '')
    {
        $sql = "SELECT * FROM {$this->table} WHERE departamento_id IS NULL AND empleado_id IS NULL";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (codigo_inventario LIKE :search1 OR numero_serie LIKE :search2 OR marca LIKE :search3 OR modelo LIKE :search4 OR tipo LIKE :search5)";
            $params[':search1'] = "%$search%";
            $params[':search2'] = "%$search%";
            $params[':search3'] = "%$search%";
            $params[':search4'] = "%$search%";
            $params[':search5'] = "%$search%";
        }

        $sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function countUnassigned(string $search = ''): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE departamento_id IS NULL AND empleado_id IS NULL";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (codigo_inventario LIKE :search1 OR numero_serie LIKE :search2 OR marca LIKE :search3 OR modelo LIKE :search4 OR tipo LIKE :search5)";
            $params[':search1'] = "%$search%";
            $params[':search2'] = "%$search%";
            $params[':search3'] = "%$search%";
            $params[':search4'] = "%$search%";
            $params[':search5'] = "%$search%";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? (int)$result->total : 0;
    }

    public function findAssigned()
    {
        $sql = "SELECT 
                    e.*, 
                    d.nombre AS departamento_nombre,
                    CONCAT(emp.nombre, ' ', emp.apellido) AS empleado_nombre
                FROM {$this->table} e
                LEFT JOIN departamentos d ON e.departamento_id = d.id
                LEFT JOIN empleados emp ON e.empleado_id = emp.id
                WHERE e.departamento_id IS NOT NULL OR e.empleado_id IS NOT NULL
                ORDER BY e.id DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findBySerialOrCodigo(string $query)
    {
        $sql = "
            SELECT 
                e.*, 
                d.nombre AS departamento_nombre
            FROM {$this->table} e
            LEFT JOIN departamentos d ON e.departamento_id = d.id
            WHERE e.codigo_inventario = ? OR e.numero_serie = ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$query, $query]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function findAllByEmpleadoCedula(string $cedula)
    {
        $sql = "
            SELECT 
                e.*, 
                d.nombre AS departamento_nombre,
                emp.nombre AS empleado_nombre,
                emp.apellido AS empleado_apellido
            FROM {$this->table} e
            INNER JOIN empleados emp ON e.empleado_id = emp.id
            LEFT JOIN departamentos d ON e.departamento_id = d.id
            WHERE emp.cedula = ? AND e.empleado_id IS NOT NULL
            ORDER BY e.codigo_inventario ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cedula]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Mantenimiento extends Model
{
    protected $table = 'mantenimientos';

    public function getByEquipoId($equipo_id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE equipo_id = ? ORDER BY fecha DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$equipo_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findLatest($limit = 5)
    {
        $sql = "
            SELECT 
                m.*, 
                e.codigo_inventario AS equipo_codigo,
                e.tipo AS equipo_tipo
            FROM {$this->table} m
            LEFT JOIN equipos e ON m.equipo_id = e.id
            ORDER BY m.fecha DESC
            LIMIT ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findAllWithDetails()
    {
        $sql = "
            SELECT 
                m.*, 
                e.codigo_inventario AS equipo_codigo,
                e.tipo AS equipo_tipo,
                e.marca AS equipo_marca,
                e.modelo AS equipo_modelo
            FROM {$this->table} m
            LEFT JOIN equipos e ON m.equipo_id = e.id
            ORDER BY m.proxima_fecha ASC, m.fecha DESC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getPendientes()
    {
        $sql = "
            SELECT 
                m.*, 
                e.codigo_inventario AS equipo_codigo,
                e.tipo AS equipo_tipo
            FROM {$this->table} m
            LEFT JOIN equipos e ON m.equipo_id = e.id
            WHERE m.estado IN ('pendiente', 'en_proceso')
            ORDER BY m.proxima_fecha ASC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getProximosVencer($dias = 30)
    {
        $sql = "
            SELECT 
                m.*, 
                e.codigo_inventario AS equipo_codigo,
                e.tipo AS equipo_tipo
            FROM {$this->table} m
            LEFT JOIN equipos e ON m.equipo_id = e.id
            WHERE m.proxima_fecha IS NOT NULL 
            AND m.proxima_fecha BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            AND m.estado NOT IN ('completado', 'cancelado')
            ORDER BY m.proxima_fecha ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$dias]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (equipo_id, fecha, tipo_mantenimiento, estado, descripcion, costo, realizado_por, tecnico_id, proxima_fecha, frecuencia, checklist, observaciones) 
                VALUES (:equipo_id, :fecha, :tipo_mantenimiento, :estado, :descripcion, :costo, :realizado_por, :tecnico_id, :proxima_fecha, :frecuencia, :checklist, :observaciones)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'equipo_id' => $data['equipo_id'],
            'fecha' => $data['fecha'],
            'tipo_mantenimiento' => $data['tipo_mantenimiento'],
            'estado' => $data['estado'] ?? 'pendiente',
            'descripcion' => $data['descripcion'],
            'costo' => $data['costo'] ?? 0,
            'realizado_por' => $data['realizado_por'] ?? null,
            'tecnico_id' => $data['tecnico_id'] ?? null,
            'proxima_fecha' => $data['proxima_fecha'] ?? null,
            'frecuencia' => $data['frecuencia'] ?? 'unica',
            'checklist' => $data['checklist'] ?? null,
            'observaciones' => $data['observaciones'] ?? null
        ]);
        return $this->pdo->lastInsertId();
    }

    public function completar($id, $observaciones = null, $costo = null)
    {
        // Obtener el registro actual
        $mantenimiento = $this->findById($id);
        if (!$mantenimiento) {
            return false;
        }

        // Calcular próxima fecha si es recurrente
        $proxima_fecha = null;
        if ($mantenimiento->frecuencia && $mantenimiento->frecuencia != 'unica') {
            $intervalo = match($mantenimiento->frecuencia) {
                'mensual' => '+1 month',
                'trimestral' => '+3 months',
                'semestral' => '+6 months',
                'anual' => '+1 year',
                default => null
            };
            if ($intervalo) {
                $proxima_fecha = date('Y-m-d', strtotime($intervalo));
            }
        }

        $datos = [
            'estado' => 'completado',
            'proxima_fecha' => $proxima_fecha
        ];

        if ($observaciones !== null) {
            $datos['observaciones'] = $observaciones;
        }
        if ($costo !== null) {
            $datos['costo'] = $costo;
        }

        return $this->update($id, $datos);
    }
    public function findAllByDepartment($departamentoId)
    {
        $sql = "
            SELECT 
                m.*, 
                e.codigo_inventario AS equipo_codigo,
                e.tipo AS equipo_tipo,
                e.marca AS equipo_marca,
                e.modelo AS equipo_modelo
            FROM {$this->table} m
            LEFT JOIN equipos e ON m.equipo_id = e.id
            WHERE e.departamento_id = ?
            ORDER BY m.proxima_fecha ASC, m.fecha DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$departamentoId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findLatestByDepartment(int $departamentoId, int $limit = 5)
    {
        $sql = "
            SELECT 
                m.*, 
                e.codigo_inventario AS equipo_codigo,
                e.tipo AS equipo_tipo
            FROM {$this->table} m
            LEFT JOIN equipos e ON m.equipo_id = e.id
            WHERE e.departamento_id = ?
            ORDER BY m.fecha DESC
            LIMIT ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $departamentoId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}

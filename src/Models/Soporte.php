<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Soporte extends Model
{
    protected $table = 'soportes';

    public $id;
    public $fecha;
    public $descripcion;
    public $observaciones;
    public $estado;
    public $prioridad;
    public $equipo_id;
    public $categoria_id;
    public $empleado_id;
    public $usuario_creacion_id;
    public $tiempo_atencion_minutos;
    public $firma_usuario;

    public function findAllWithDetails()
    {
        $sql = "
            SELECT 
                s.*, 
                e.numero_serie AS equipo_serial, 
                e.tipo AS equipo_tipo,
                d.nombre AS departamento_nombre,
                CONCAT(emp.nombre, ' ', emp.apellido) AS tecnico_asignado,
                c.nombre AS categoria_nombre,
                c.color AS categoria_color,
                c.icono AS categoria_icono,
                sol.id AS solicitante_id,
                CONCAT(sol.nombre, ' ', IFNULL(sol.apellido, '')) AS solicitante_nombre,
                (SELECT COUNT(*) FROM ticket_comentarios tc WHERE tc.ticket_id = s.id) AS comentarios_count
            FROM {$this->table} s
            JOIN equipos e ON s.equipo_id = e.id
            JOIN departamentos d ON e.departamento_id = d.id
            LEFT JOIN empleados emp ON s.empleado_id = emp.id
            LEFT JOIN usuarios u ON emp.usuario_id = u.id
            LEFT JOIN categorias c ON s.categoria_id = c.id
            LEFT JOIN empleados sol ON e.empleado_id = sol.id
            ORDER BY s.fecha DESC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getSoportesByEquipoId(int $equipoId)
    {
        $sql = "
            SELECT 
                s.*, 
                u.username AS tecnico_asignado
            FROM {$this->table} s
            LEFT JOIN empleados emp ON s.empleado_id = emp.id
            LEFT JOIN usuarios u ON emp.usuario_id = u.id
            WHERE s.equipo_id = ?
            ORDER BY s.fecha DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$equipoId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findByIdWithDetails(int $id)
    {
        $sql = "
            SELECT 
                s.*, 
                eq.codigo_inventario AS equipo_codigo,
                eq.numero_serie AS equipo_serial, 
                eq.tipo AS equipo_tipo,
                eq.marca AS equipo_marca,
                eq.modelo AS equipo_modelo,
                eq.empleado_id AS equipo_empleado_id,
                d.nombre AS departamento_nombre,
                d.ubicacion AS departamento_ubicacion,
                CONCAT(emp.nombre, ' ', IFNULL(emp.apellido, '')) AS tecnico_asignado,
                u.username AS tecnico_username,
                s.observaciones AS soporte_observaciones,
                c.nombre AS categoria_nombre,
                c.color AS categoria_color,
                c.icono AS categoria_icono,
                CONCAT(emp_equipo.nombre, ' ', IFNULL(emp_equipo.apellido, '')) AS equipo_empleado_nombre,
                u_creador.username AS usuario_nombre,
                u_creador.id AS usuario_creador_id
            FROM {$this->table} s
            LEFT JOIN equipos eq ON s.equipo_id = eq.id
            LEFT JOIN departamentos d ON eq.departamento_id = d.id
            LEFT JOIN empleados emp ON s.empleado_id = emp.id
            LEFT JOIN usuarios u ON emp.usuario_id = u.id
            LEFT JOIN categorias c ON s.categoria_id = c.id
            LEFT JOIN empleados emp_equipo ON eq.empleado_id = emp_equipo.id
            LEFT JOIN usuarios u_creador ON s.usuario_creacion_id = u_creador.id
            WHERE s.id = ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function create(array $data)
    {
        if (empty($data['descripcion']) || empty($data['equipo_id'])) {
            return false;
        }


        $prioridad = $data['prioridad'] ?? 'media';
        $categoria_id = !empty($data['categoria_id']) ? $data['categoria_id'] : null;
        
        // Calcular fecha de vencimiento usando configuración
        $slaConfig = require __DIR__ . '/../../config/sla.php';
        $horas = $slaConfig[$prioridad] ?? $slaConfig['media']; // Fallback a media si no existe
        
        $fecha_vencimiento = date('Y-m-d H:i:s', strtotime("+$horas hours"));

        $sql = "INSERT INTO {$this->table} (descripcion, equipo_id, usuario_creacion_id, prioridad, categoria_id, estado, fecha, fecha_vencimiento) 
                VALUES (?, ?, ?, ?, ?, 'pendiente', NOW(), ?)";
        
        $stmt = $this->pdo->prepare($sql);
        
        if ($stmt->execute([
            $data['descripcion'],
            $data['equipo_id'],
            $data['usuario_creacion_id'] ?? null,
            $prioridad,
            $categoria_id,
            $fecha_vencimiento
        ])) {
            return (int) $this->pdo->lastInsertId();
        }

        return false;
    }

    public function update(int $id, array $data)
    {
        $fields = [];
        $values = [];

        if (isset($data['descripcion'])) {
            $fields[] = "descripcion = ?";
            $values[] = $data['descripcion'];
        }
        if (isset($data['observaciones'])) {
            $fields[] = "observaciones = ?";
            $values[] = $data['observaciones'];
        }
        if (isset($data['estado'])) {
            $fields[] = "estado = ?";
            $values[] = $data['estado'];
        }
        if (isset($data['prioridad'])) {
            $fields[] = "prioridad = ?";
            $values[] = $data['prioridad'];

            // Recalcular fecha de vencimiento si cambia la prioridad usando configuración
            $slaConfig = require __DIR__ . '/../../config/sla.php';
            $horas = $slaConfig[$data['prioridad']] ?? $slaConfig['media'];
            
            $fields[] = "fecha_vencimiento = ?";
            $values[] = date('Y-m-d H:i:s', strtotime("+$horas hours"));
        }
        if (isset($data['categoria_id'])) {
            $fields[] = "categoria_id = ?";
            $values[] = $data['categoria_id'];
        }
        if (isset($data['empleado_id'])) {
            $fields[] = "empleado_id = ?";
            $values[] = $data['empleado_id'];
        }
        if (isset($data['fecha_cierre'])) {
            $fields[] = "fecha_cierre = ?";
            $values[] = $data['fecha_cierre'];
        }
        if (isset($data['tiempo_atencion_minutos'])) {
            $fields[] = "tiempo_atencion_minutos = ?";
            $values[] = $data['tiempo_atencion_minutos'];
        }
        
        // Campos de valoración
        if (isset($data['valoracion'])) {
            $fields[] = "valoracion = ?";
            $values[] = $data['valoracion'];
        }
        if (isset($data['comentario_valoracion'])) {
            $fields[] = "comentario_valoracion = ?";
            $values[] = $data['comentario_valoracion'];
        }
        if (isset($data['fecha_valoracion'])) {
            $fields[] = "fecha_valoracion = ?";
            $values[] = $data['fecha_valoracion'];
        }

        if (empty($fields)) {
            return false;
        }

        // Optimistic Locking: Increment version_id
        $fields[] = "version_id = version_id + 1";

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        
        // If version_id is provided, use it in WHERE clause for optimistic locking
        if (isset($data['current_version'])) {
            $sql .= " AND version_id = ?";
            $values[] = $id;
            $values[] = $data['current_version'];
        } else {
            $values[] = $id;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        
        // Return true if exactly 1 row was updated (if 0, version conflict occurred or ID not found)
        return $stmt->rowCount() > 0;
    }

    public function assign(int $soporte_id, int $tecnico_id)
    {
        $sql = "UPDATE {$this->table} 
                SET empleado_id = ?, estado = 'en_proceso' 
                WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$tecnico_id, $soporte_id]);
    }

    public function findByUsuarioId(?int $usuarioId)
    {
        if (!$usuarioId) return [];
        
        $sql = "
            SELECT 
                s.*, 
                e.numero_serie AS equipo_serial, 
                e.tipo AS equipo_tipo,
                d.nombre AS departamento_nombre,
                u.username AS tecnico_asignado
            FROM {$this->table} s
            JOIN equipos e ON s.equipo_id = e.id
            JOIN departamentos d ON e.departamento_id = d.id
            LEFT JOIN empleados emp ON s.empleado_id = emp.id
            LEFT JOIN usuarios u ON emp.usuario_id = u.id
            WHERE s.usuario_creacion_id = ?
            ORDER BY s.fecha DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getDashboardStats()
    {
        $sql = "
            SELECT 
                COUNT(id) AS total,
                SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) AS pendiente,
                SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) AS en_proceso,
                SUM(CASE WHEN estado = 'resuelto' AND DATE(fecha_cierre) = CURDATE() THEN 1 ELSE 0 END) AS resuelto
            FROM {$this->table}
        ";
        
        $stmt = $this->pdo->query($sql);
        $stats = $stmt->fetch(PDO::FETCH_OBJ);

        $stats->total       = $stats->total ?? 0;
        $stats->pendiente   = $stats->pendiente ?? 0;
        $stats->en_proceso  = $stats->en_proceso ?? 0;
        $stats->resuelto    = $stats->resuelto ?? 0;
        
        return $stats;
    }

    public function findLatestPending(int $limit = 5): array
    {
        $sql = "
            SELECT 
                s.*, 
                e.numero_serie AS equipo_serial, 
                e.tipo AS equipo_tipo,
                d.nombre AS departamento_nombre,
                u.username AS tecnico_asignado
            FROM {$this->table} s
            JOIN equipos e ON s.equipo_id = e.id
            JOIN departamentos d ON e.departamento_id = d.id
            LEFT JOIN empleados emp ON s.empleado_id = emp.id
            LEFT JOIN usuarios u ON emp.usuario_id = u.id
            WHERE s.estado = 'pendiente'
            ORDER BY s.fecha DESC
            LIMIT ?
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findLatestInProcess(int $limit = 5): array
    {
        $sql = "
            SELECT 
                s.*, 
                e.numero_serie AS equipo_serial, 
                e.tipo AS equipo_tipo,
                d.nombre AS departamento_nombre,
                u.username AS tecnico_asignado
            FROM {$this->table} s
            JOIN equipos e ON s.equipo_id = e.id
            JOIN departamentos d ON e.departamento_id = d.id
            LEFT JOIN empleados emp ON s.empleado_id = emp.id
            LEFT JOIN usuarios u ON emp.usuario_id = u.id
            WHERE s.estado = 'en_proceso'
            ORDER BY s.fecha DESC
            LIMIT ?
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    public function getConsumos(int $soporteId)
    {
        $sql = "
            SELECT 
                ic.*, 
                ii.nombre AS item_nombre, 
                ii.codigo AS item_codigo,
                u.username AS registrado_por
            FROM inventario_consumos ic
            JOIN inventario_items ii ON ic.item_id = ii.id
            JOIN usuarios u ON ic.usuario_id = u.id
            WHERE ic.soporte_id = ?
            ORDER BY ic.fecha DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$soporteId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    public function findAllByDepartment(int $departamentoId)
    {
        $sql = "
            SELECT 
                s.*, 
                e.numero_serie AS equipo_serial, 
                e.tipo AS equipo_tipo,
                d.nombre AS departamento_nombre,
                u.username AS tecnico_asignado
            FROM {$this->table} s
            JOIN equipos e ON s.equipo_id = e.id
            JOIN departamentos d ON e.departamento_id = d.id
            LEFT JOIN empleados emp ON s.empleado_id = emp.id
            LEFT JOIN usuarios u ON emp.usuario_id = u.id
            WHERE e.departamento_id = ?
            ORDER BY s.fecha DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$departamentoId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getDashboardStatsByDepartment(int $departamentoId)
    {
        $stats = [
            'total' => 0,
            'pendiente' => 0,
            'en_proceso' => 0,
            'resuelto' => 0
        ];

        $sql = "
            SELECT s.estado, COUNT(*) as count 
            FROM {$this->table} s
            JOIN equipos e ON s.equipo_id = e.id
            WHERE e.departamento_id = ?
            GROUP BY s.estado
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$departamentoId]);
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);

        foreach ($results as $row) {
            $stats['total'] += $row->count;
            if (isset($stats[$row->estado])) {
                $stats[$row->estado] = $row->count;
            }
            // Map specific statuses if needed, e.g., 'asignado' -> 'en_proceso'
            if ($row->estado === 'asignado') {
                 $stats['en_proceso'] += $row->count;
            }
        }

        return (object)$stats;
    }

    public function findLatestPendingByDepartment(int $departamentoId, int $limit = 5)
    {
        $sql = "
            SELECT s.*, e.codigo_inventario 
            FROM {$this->table} s
            JOIN equipos e ON s.equipo_id = e.id
            WHERE e.departamento_id = ? AND s.estado = 'pendiente'
            ORDER BY s.fecha DESC
            LIMIT ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $departamentoId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findLatestInProcessByDepartment(int $departamentoId, int $limit = 5)
    {
        $sql = "
            SELECT s.*, e.codigo_inventario 
            FROM {$this->table} s
            JOIN equipos e ON s.equipo_id = e.id
            WHERE e.departamento_id = ? AND s.estado IN ('en_proceso', 'asignado')
            ORDER BY s.fecha DESC
            LIMIT ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $departamentoId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Cuenta tickets activos asignados a un empleado
     * Estados activos: pendiente, en_proceso, en_espera
     * @param int $empleadoId
     * @return int
     */
    public function countActiveByEmpleado(int $empleadoId): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE empleado_id = ? 
                AND estado IN ('pendiente', 'en_proceso', 'en_espera')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$empleadoId]);
        return (int) $stmt->fetchColumn();
    }

    public function delete(int $id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Obtener estadísticas de tickets por prioridad
     */
    public function getTicketsByPriority()
    {
        $sql = "SELECT prioridad, COUNT(*) as total FROM {$this->table} GROUP BY prioridad";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Obtener estadísticas de tickets por categoría
     */
    public function getTicketsByCategory()
    {
        $sql = "SELECT c.nombre, COUNT(s.id) as total 
                FROM {$this->table} s 
                LEFT JOIN categorias c ON s.categoria_id = c.id 
                GROUP BY c.nombre";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Obtener tickets por mes (últimos 6 meses)
     */
    public function getTicketsByMonth($limit = 6)
    {
        $sql = "SELECT DATE_FORMAT(fecha, '%Y-%m') as mes, COUNT(*) as total 
                FROM {$this->table} 
                WHERE fecha >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                GROUP BY mes 
                ORDER BY mes ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Calcular tiempo promedio de atención (en minutos)
     */
    public function getAverageResolutionTime()
    {
        $sql = "SELECT AVG(tiempo_atencion_minutos) as promedio 
                FROM {$this->table} 
                WHERE estado = 'resuelto' AND tiempo_atencion_minutos IS NOT NULL";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchColumn();
    }

    /**
     * Obtener top técnicos por tickets resueltos
     */
    public function getTopTechnicians($limit = 5)
    {
        $sql = "SELECT u.username, COUNT(s.id) as total 
                FROM {$this->table} s
                JOIN empleados e ON s.empleado_id = e.id
                JOIN usuarios u ON e.usuario_id = u.id
                WHERE s.estado = 'resuelto'
                GROUP BY u.username
                ORDER BY total DESC
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Buscar soportes con filtros dinámicos
     */
    public function findAllWithFilters(array $filters)
    {
        $sql = "
            SELECT 
                s.*, 
                e.numero_serie AS equipo_serial, 
                e.tipo AS equipo_tipo,
                d.nombre AS departamento_nombre,
                u.username AS tecnico_asignado,
                c.nombre AS categoria_nombre,
                c.color AS categoria_color,
                c.icono AS categoria_icono
            FROM {$this->table} s
            JOIN equipos e ON s.equipo_id = e.id
            JOIN departamentos d ON e.departamento_id = d.id
            LEFT JOIN empleados emp ON s.empleado_id = emp.id
            LEFT JOIN usuarios u ON emp.usuario_id = u.id
            LEFT JOIN categorias c ON s.categoria_id = c.id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['fecha_inicio'])) {
            $sql .= " AND DATE(s.fecha) >= ?";
            $params[] = $filters['fecha_inicio'];
        }

        if (!empty($filters['fecha_fin'])) {
            $sql .= " AND DATE(s.fecha) <= ?";
            $params[] = $filters['fecha_fin'];
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND s.estado = ?";
            $params[] = $filters['estado'];
        }

        if (!empty($filters['categoria_id'])) {
            $sql .= " AND s.categoria_id = ?";
            $params[] = $filters['categoria_id'];
        }

        if (!empty($filters['prioridad'])) {
            $sql .= " AND s.prioridad = ?";
            $params[] = $filters['prioridad'];
        }

        $sql .= " ORDER BY s.fecha DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}

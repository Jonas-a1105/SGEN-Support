<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Usuario extends Model
{
    protected $table = 'usuarios';

    /**
     * Busca un usuario por su username.
     * @param string $username
     * @return object|null
     */
    public function findByUsername(string $username)
    {
        $sql = "SELECT * FROM {$this->table} WHERE username = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Busca todos los usuarios que son técnicos y están vinculados a un empleado.
     * @return array
     */
    public function findAllTechnicians(): array
    {
        $sql = "
            SELECT 
                u.id as usuario_id,
                u.username, 
                e.id as empleado_id, 
                CONCAT(e.nombre, ' ', e.apellido) as nombre_completo
            FROM {$this->table} u
            JOIN empleados e ON u.id = e.usuario_id
            WHERE u.rol = 'tecnico'
            ORDER BY nombre_completo ASC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Busca usuarios que no están vinculados a un empleado.
     * @param int|null $currentUserId El ID del usuario actualmente vinculado (en modo edición).
     * @return array
     */
    public function findAvailable(int $currentUserId = null): array
    {
        // Esta consulta selecciona todos los usuarios que
        // 1. No están en la tabla de empleados (e.usuario_id IS NULL)
        // 2. O son el usuario que ya está asignado a este empleado (u.id = ?)
        $sql = "
            SELECT u.* FROM {$this->table} u
            LEFT JOIN empleados e ON u.id = e.usuario_id
            WHERE e.usuario_id IS NULL OR u.id = ?
            ORDER BY u.username ASC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        // Usamos $currentUserId ?? 0 para manejar el caso de 'crear' (donde es null)
        $stmt->execute([$currentUserId ?? 0]); 
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Busca todos los usuarios con rol admin.
     * @return array
     */
    public function findAllAdmins(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE rol = 'admin' ORDER BY username ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Busca todos los usuarios con detalles del empleado y departamento vinculados.
     * @return array
     */
    public function findAllWithDetails(): array
    {
        $sql = "
            SELECT 
                u.*,
                e.id as empleado_id,
                e.nombre as empleado_nombre,
                e.apellido as empleado_apellido,
                e.email as empleado_email,
                d.id as departamento_id,
                d.nombre as departamento_nombre
            FROM {$this->table} u
            LEFT JOIN empleados e ON u.empleado_id = e.id
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            ORDER BY 
                CASE u.rol 
                    WHEN 'admin' THEN 1 
                    WHEN 'tecnico' THEN 2 
                    WHEN 'consultor' THEN 3 
                    ELSE 4 
                END,
                u.username ASC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
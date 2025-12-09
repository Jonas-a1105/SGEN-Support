<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Empleado extends Model
{
    protected $table = 'empleados';

    public function findAllWithDetails()
    {
        $sql = "
            SELECT 
                e.*, 
                u.username AS usuario_username,
                u.rol AS usuario_rol,
                d.nombre AS departamento_nombre
            FROM {$this->table} e
            LEFT JOIN usuarios u ON e.usuario_id = u.id
            LEFT JOIN departamentos d ON e.departamento_id = d.id
            ORDER BY e.nombre ASC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findByDepartamentoId(int $departamento_id)
    {
        $sql = "
            SELECT 
                e.*, 
                u.username AS usuario_username,
                d.nombre AS departamento_nombre
            FROM {$this->table} e
            LEFT JOIN usuarios u ON e.usuario_id = u.id
            LEFT JOIN departamentos d ON e.departamento_id = d.id
            WHERE e.departamento_id = ?
            ORDER BY e.nombre ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$departamento_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findByEmail(string $email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function findByCedula(string $cedula)
    {
        $sql = "
            SELECT 
                e.*, 
                u.username AS usuario_username
            FROM {$this->table} e
            LEFT JOIN usuarios u ON e.usuario_id = u.id
            WHERE e.cedula = ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cedula]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function findByUsuarioId($usuario_id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function findByIdWithDetails($id)
    {
        $sql = "
            SELECT 
                e.*, 
                u.username AS usuario_username,
                d.nombre AS departamento_nombre
            FROM {$this->table} e
            LEFT JOIN usuarios u ON e.usuario_id = u.id
            LEFT JOIN departamentos d ON e.departamento_id = d.id
            WHERE e.id = ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Busca empleados sin departamento asignado
     */
    public function findWithoutDepartment()
    {
        $sql = "
            SELECT 
                e.*, 
                u.username AS usuario_username
            FROM {$this->table} e
            LEFT JOIN usuarios u ON e.usuario_id = u.id
            WHERE e.departamento_id IS NULL
            ORDER BY e.nombre ASC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    /**
     * Desvincula un usuario de los empleados que lo tengan asignado.
     * @param int $usuarioId
     * @return bool
     */
    public function desvincularUsuario(int $usuarioId): bool
    {
        $sql = "UPDATE {$this->table} SET usuario_id = NULL WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$usuarioId]);
    }
}
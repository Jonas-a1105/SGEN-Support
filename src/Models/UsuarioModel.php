<?php
namespace App\Models;

use PDO;

class UsuarioModel {
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtener usuario por ID
     */
    public function findById(int $id) {
        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Obtener usuario por nombre de usuario
     */
    public function findByUsername(string $username) {
        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE username = :username');
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Actualizar referencia a empleado y departamento
     */
    public function updateReferences(int $userId, ?int $empleadoId, ?int $departamentoId) {
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios SET empleado_id = :emp, departamento_id = :dept WHERE id = :id'
        );
        return $stmt->execute([
            'emp' => $empleadoId,
            'dept' => $departamentoId,
            'id' => $userId
        ]);
    }

    /**
     * Listar todos los usuarios (admin only)
     */
    public function getAll() {
        $stmt = $this->pdo->query('SELECT * FROM usuarios');
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
?>

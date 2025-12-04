<?php
namespace App\Core;
use PDO;

abstract class Model {
    protected $pdo;
    protected $table; 

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    /**
     * Inserta un nuevo registro en la tabla.
     * @param array $data ['columna' => 'valor', ...]
     * @return int|false El ID del nuevo registro o false si falla.
     */
    public function create(array $data) {
        $fields = array_keys($data);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        $columns = implode(', ', $fields);

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $this->pdo->prepare($sql);
        
        // --- LÓGICA CORREGIDA ---
        if ($stmt->execute(array_values($data))) {
            return (int) $this->pdo->lastInsertId();
        }
        return false;
    }
    
    public function findAll() {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    public function findById(int $id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function update(int $id, array $data) {
        $setClauses = [];
        foreach (array_keys($data) as $field) {
            $setClauses[] = "{$field} = ?";
        }
        $set = implode(', ', $setClauses);

        $sql = "UPDATE {$this->table} SET {$set} WHERE id = ?";
        
        $values = array_values($data);
        $values[] = $id;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete(int $id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
}
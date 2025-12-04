<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Categoria extends Model
{
    protected $table = 'categorias';

    public $id;
    public $nombre;
    public $descripcion;
    public $icono;
    public $color;
    public $activo;

    // Constructor removed to use parent constructor from Model

    /**
     * Obtener todas las categorías activas
     */
    public function findAllActive()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE activo = 1 ORDER BY nombre ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Buscar por ID
     */
    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
}

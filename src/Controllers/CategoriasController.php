<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Categoria;

class CategoriasController extends Controller
{
    private $categoriaModel;

    public function __construct()
    {
        $this->categoriaModel = new Categoria();
    }

    public function index()
    {
        $categorias = $this->categoriaModel->findAllActive();
        $this->render('categorias/index', [
            'categorias' => $categorias,
            'titulo' => 'Gestión de Categorías'
        ]);
    }

    public function crear()
    {
        $this->render('categorias/formulario', [
            'titulo' => 'Nueva Categoría',
            'categoria' => null
        ]);
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => $_POST['nombre'] ?? '',
                'descripcion' => $_POST['descripcion'] ?? '',
                'icono' => $_POST['icono'] ?? 'bi-tools',
                'color' => $_POST['color'] ?? '#6c757d',
                'activo' => 1
            ];

            if (empty($data['nombre'])) {
                $this->setFlashMessage('error', 'El nombre de la categoría es obligatorio.');
                header('Location: ' . BASE_URL . 'categorias/crear');
                return;
            }

            if ($this->categoriaModel->create($data)) {
                $this->setFlashMessage('success', 'Categoría creada correctamente.');
                header('Location: ' . BASE_URL . 'categorias');
            } else {
                $this->setFlashMessage('error', 'Error al guardar la categoría.');
                header('Location: ' . BASE_URL . 'categorias/crear');
            }
        }
    }

    public function editar($id)
    {
        $categoria = $this->categoriaModel->findById($id);
        if (!$categoria) {
            header('Location: ' . BASE_URL . 'categorias');
            return;
        }

        $this->render('categorias/formulario', [
            'titulo' => 'Editar Categoría',
            'categoria' => $categoria
        ]);
    }

    public function actualizar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => $_POST['nombre'] ?? '',
                'descripcion' => $_POST['descripcion'] ?? '',
                'icono' => $_POST['icono'] ?? 'bi-tools',
                'color' => $_POST['color'] ?? '#6c757d'
            ];

            if ($this->categoriaModel->update($id, $data)) {
                $this->setFlashMessage('success', 'Categoría actualizada correctamente.');
                header('Location: ' . BASE_URL . 'categorias');
            } else {
                $this->setFlashMessage('error', 'Error al actualizar la categoría.');
                header('Location: ' . BASE_URL . 'categorias/editar/' . $id);
            }
        }
    }

    public function eliminar($id)
    {
        // Soft delete: cambiar activo a 0
        if ($this->categoriaModel->update($id, ['activo' => 0])) {
            $this->setFlashMessage('success', 'Categoría eliminada correctamente.');
        } else {
            $this->setFlashMessage('error', 'Error al eliminar la categoría.');
        }
        header('Location: ' . BASE_URL . 'categorias');
    }
}

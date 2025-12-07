<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator; 
use App\Models\Departamento;
use PDOException; 

class DepartamentosController extends Controller
{
    private $departamentoModel;
    
    public function __construct() { 
        parent::__construct(); 
        $this->restrictTo(['admin', 'tecnico', 'consultor']); 
        $this->departamentoModel = new Departamento(); 
    }
    
    public function index() { 
        if ($_SESSION['rol'] === 'admin') {
            // Obtener departamentos con estadísticas de empleados y equipos
            $departamentos = $this->departamentoModel->findAllWithStats(); 
        } else {
            // Tecnico/Consultor see only their department
            $deptId = $_SESSION['departamento_id'];
            $departamentos = [];
            if ($deptId) {
                $dept = $this->departamentoModel->findById($deptId);
                if ($dept) {
                    // Obtener estadísticas para este departamento
                    $equipoModel = new \App\Models\Equipo();
                    $empleadoModel = new \App\Models\Empleado();
                    $dept->empleados_count = count($empleadoModel->findByDepartamentoId($deptId));
                    $dept->equipos_count = count($equipoModel->findByDepartamentoId($deptId));
                    $departamentos[] = $dept;
                }
            }
        }
        $this->render('departamentos/lista', [ 'titulo' => 'Gestión de Departamentos', 'departamentos' => $departamentos ]); 
    }
    
    public function crear() { 
        $this->restrictTo(['admin']);
        
        // Obtener lista de empleados para el select de jefe de área
        $empleadoModel = new \App\Models\Empleado();
        $empleados = $empleadoModel->findAll();
        
        $this->render('departamentos/formulario', [ 
            'titulo' => 'Crear Nuevo Departamento', 
            'departamento' => null,
            'empleados' => $empleados
        ]); 
    }
    
    public function editar(int $id) { 
        $this->restrictTo(['admin']);
        $departamento = $this->departamentoModel->findById($id); 
        if (!$departamento) { 
            $this->setFlashMessage('error', 'Departamento no encontrado.'); 
            header('Location: ' . BASE_URL . 'departamentos'); 
            exit; 
        }
        
        // Obtener lista de empleados para el select de jefe de área
        $empleadoModel = new \App\Models\Empleado();
        $empleados = $empleadoModel->findAll();
        
        $this->render('departamentos/formulario', [ 
            'titulo' => "Editar Departamento: {$departamento->nombre}", 
            'departamento' => $departamento,
            'empleados' => $empleados
        ]); 
    }

    public function guardar()
    {
        $this->restrictTo(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
            header('Location: ' . BASE_URL . 'departamentos');
            exit;
        }
        
        $validator = new Validator($_POST);
        $id = $validator->getInt('id'); 
        $es_edicion = !empty($id);

        $validator->check('nombre', 'required');
        if ($validator->fails()) { 
            $this->setFlashMessage('error', $validator->getErrors()[array_key_first($validator->getErrors())]);
            header('Location: '. $_SERVER['HTTP_REFERER']);
            exit;
        }

        $datos = [ 
            'nombre' => $validator->get('nombre'),
            'ubicacion' => $validator->get('ubicacion'),
            'descripcion' => $validator->get('descripcion'),
            'jefe_area_id' => $validator->getInt('jefe_area_id') ?: null,
            'jefe_area_nombre' => $validator->get('jefe_area_nombre') ?: null
        ];

        try {
            if ($es_edicion) {
                if ($this->departamentoModel->update($id, $datos)) {
                    $this->setFlashMessage('success', "Departamento #{$id} actualizado.");
                    $this->logBitacora("Actualizó el departamento {$datos['nombre']}", 'departamento', $id);
                }
            } else {
                if ($newId = $this->departamentoModel->create($datos)) {
                    $this->setFlashMessage('success', 'Departamento creado.');
                    $this->logBitacora("Creó el departamento {$datos['nombre']}", 'departamento', $newId);
                }
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') { 
                $this->setFlashMessage('error', 'Error: El departamento "' . $datos['nombre'] . '" ya existe.'); 
            }
            else { 
                $this->setFlashMessage('error', 'Error de BD: ' . $e->getMessage()); 
            }
            header('Location: ' . $_SERVER['HTTP_REFERER']); 
            exit;
        }
        header("Location: " . BASE_URL . "departamentos"); 
        exit;
    }

    public function eliminar(int $id)
    {
        $this->restrictTo(['admin']);
        $departamento = $this->departamentoModel->findById($id); 
        if (!$departamento) { 
            $this->setFlashMessage('error', 'Departamento no encontrado.'); 
            header('Location: ' . BASE_URL . 'departamentos'); 
            exit;
        }

        try {
            if ($this->departamentoModel->delete($id)) {
                $this->setFlashMessage('success', "Departamento #{$id} eliminado.");
                $this->logBitacora("Eliminó el departamento {$departamento->nombre}", 'departamento', $id);
            } else { 
                $this->setFlashMessage('error', "Error al eliminar."); 
            }
        } catch (PDOException $e) {
             $this->setFlashMessage('error', "No se puede eliminar: el departamento tiene equipos asociados.");
        }
        header('Location: ' . BASE_URL . 'departamentos'); 
        exit;
    }

    public function ver(int $id)
    {
        // Non-admins can only view their own department
        if ($_SESSION['rol'] !== 'admin' && $id != $_SESSION['departamento_id']) {
            $this->setFlashMessage('error', 'Acceso no autorizado a este departamento.');
            header('Location: ' . BASE_URL . 'departamentos');
            exit;
        }

        $departamento = $this->departamentoModel->findById($id);
        if (!$departamento) {
            $this->setFlashMessage('error', 'Departamento no encontrado.');
            header('Location: ' . BASE_URL . 'departamentos');
            exit;
        }

        $equipoModel = new \App\Models\Equipo();
        $equipos = $equipoModel->findByDepartamentoId($id);

        $empleadoModel = new \App\Models\Empleado();
        $empleados = $empleadoModel->findByDepartamentoId($id);

        $this->render('departamentos/ver', [
            'titulo' => "Detalle Departamento: {$departamento->nombre}",
            'departamento' => $departamento,
            'equipos' => $equipos,
            'empleados' => $empleados
        ]);
    }

    public function asignarEquipo()
    {
        $this->restrictTo(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'departamentos');
            exit;
        }

        $validator = new Validator($_POST);
        $departamento_id = $validator->getInt('departamento_id');
        $identificador = $validator->get('identificador');

        if (empty($departamento_id) || empty($identificador)) {
            $this->setFlashMessage('error', 'Debe proporcionar el ID del departamento y el serial/código del equipo.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        $equipoModel = new \App\Models\Equipo();
        
        $equipo = $equipoModel->findBySerial($identificador);
        if (!$equipo) {
            $equipo = $equipoModel->findByCodigo($identificador);
        }

        if (!$equipo) {
            $this->setFlashMessage('error', 'Equipo no encontrado con ese serial o código.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        if ($equipoModel->update($equipo->id, ['departamento_id' => $departamento_id])) {
            $this->setFlashMessage('success', "Equipo asignado correctamente al departamento.");
            $this->logBitacora("Asignó equipo {$equipo->codigo_inventario} al departamento #{$departamento_id}", 'departamento', $departamento_id);
        } else {
            $this->setFlashMessage('error', 'Error al asignar el equipo.');
        }

        header('Location: ' . BASE_URL . 'departamentos/ver/' . $departamento_id);
        exit;
    }
}
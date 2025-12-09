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

        // Fetch Inventory Items (Consumables)
        $inventarioModel = new \App\Models\Inventario();
        $itemsInventario = $inventarioModel->obtenerItemsPorDepartamento($id);
        $movimientosInventario = $inventarioModel->obtenerMovimientosPorDepartamento($id, 20);

        $this->render('departamentos/ver', [
            'titulo' => "Detalle Departamento: {$departamento->nombre}",
            'departamento' => $departamento,
            'equipos' => $equipos,
            'empleados' => $empleados,
            'itemsInventario' => $itemsInventario,
            'movimientosInventario' => $movimientosInventario
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

    /**
     * API: Get available equipment (not assigned to any department)
     */
    public function apiEquiposDisponibles()
    {
        $this->restrictTo(['admin']);
        header('Content-Type: application/json');
        
        $equipoModel = new \App\Models\Equipo();
        $equipos = $equipoModel->findUnassigned();
        
        $result = [];
        foreach ($equipos as $e) {
            $result[] = [
                'id' => $e->id,
                'name' => trim(($e->marca ?? '') . ' ' . ($e->modelo ?? $e->tipo)),
                'code' => $e->codigo_inventario ?? $e->numero_serie ?? '',
                'serial' => $e->numero_serie ?? '',
                'type' => $e->tipo ?? '',
                'status' => $e->estado === 'disponible' ? 'available' : 'maintenance'
            ];
        }
        
        echo json_encode($result);
        exit;
    }

    /**
     * API: Get available employees (not assigned to any department)
     */
    public function apiEmpleadosDisponibles()
    {
        $this->restrictTo(['admin']);
        header('Content-Type: application/json');
        
        $empleadoModel = new \App\Models\Empleado();
        $empleados = $empleadoModel->findWithoutDepartment();
        
        $result = [];
        foreach ($empleados as $emp) {
            $initials = strtoupper(substr($emp->nombre, 0, 1) . substr($emp->apellido ?? '', 0, 1));
            $result[] = [
                'id' => $emp->id,
                'name' => trim($emp->nombre . ' ' . ($emp->apellido ?? '')),
                'role' => $emp->cargo ?? 'Sin cargo',
                'avatar' => $initials,
                'cedula' => $emp->cedula ?? '',
                'status' => ($emp->activo ?? 1) ? 'active' : 'on_leave'
            ];
        }
        
        echo json_encode($result);
        exit;
    }

    /**
     * Assign an employee to a department
     */
    public function asignarEmpleado()
    {
        $this->restrictTo(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'departamentos');
            exit;
        }

        $validator = new Validator($_POST);
        $departamento_id = $validator->getInt('departamento_id');
        $empleado_id = $validator->getInt('empleado_id');

        if (empty($departamento_id) || empty($empleado_id)) {
            $this->setFlashMessage('error', 'Debe proporcionar el ID del departamento y del empleado.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        $empleadoModel = new \App\Models\Empleado();
        $empleado = $empleadoModel->findById($empleado_id);

        if (!$empleado) {
            $this->setFlashMessage('error', 'Empleado no encontrado.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        if ($empleadoModel->update($empleado_id, ['departamento_id' => $departamento_id])) {
            $this->setFlashMessage('success', "Empleado asignado correctamente al departamento.");
            $this->logBitacora("Asignó empleado {$empleado->nombre} al departamento #{$departamento_id}", 'departamento', $departamento_id);
        } else {
            $this->setFlashMessage('error', 'Error al asignar el empleado.');
        }

        header('Location: ' . BASE_URL . 'departamentos/ver/' . $departamento_id);
        exit;
    }

    /**
     * Bulk delete departments via AJAX
     */
    public function eliminar_masivo()
    {
        $this->restrictTo(['admin']);
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $ids = $input['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['success' => false, 'message' => 'No se proporcionaron IDs']);
            exit;
        }

        $deletedCount = 0;
        $errors = [];

        foreach ($ids as $id) {
            try {
                $dept = $this->departamentoModel->findById((int)$id);
                if ($dept && $this->departamentoModel->delete((int)$id)) {
                    $deletedCount++;
                    $this->logBitacora("Eliminó el departamento {$dept->nombre} (eliminación masiva)", 'departamento', $id);
                }
            } catch (PDOException $e) {
                $errors[] = "Departamento #{$id} tiene dependencias";
            }
        }

        if ($deletedCount > 0) {
            $message = "Se eliminaron {$deletedCount} departamento(s) correctamente.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " no se pudieron eliminar por dependencias.";
            }
            echo json_encode(['success' => true, 'message' => $message, 'deleted' => $deletedCount]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar ningún departamento. ' . implode(', ', $errors)]);
        }
        exit;
    }
}
<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Equipo;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Mantenimiento;
use App\Models\Soporte;
use App\Services\EquipoService;
use PDOException;

class EquiposController extends Controller
{
    private $equipoModel;
    private $departamentoModel;
    private $empleadoModel;
    private $mantenimientoModel;
    private $soporteModel;
    private $equipoService;

    public function __construct()
    {
        parent::__construct();
        $this->restrictTo(['admin', 'tecnico', 'consultor']);
        
        $this->equipoModel = new Equipo();
        $this->departamentoModel = new Departamento();
        $this->empleadoModel = new Empleado();
        $this->mantenimientoModel = new Mantenimiento();
        $this->soporteModel = new Soporte();
        
        $this->equipoService = new EquipoService();
    }

    public function index()
    {
        if ($_SESSION['rol'] === 'admin') {
            $equipos = $this->equipoModel->findAssigned();
        } elseif ($_SESSION['rol'] === 'tecnico' || $_SESSION['rol'] === 'consultor') {
            $deptId = $_SESSION['departamento_id'];
            $equipos = $deptId ? $this->equipoModel->findByDepartamentoId($deptId) : [];
        } else {
            $equipos = [];
        }
        
        $this->render('equipos/lista', [
            'titulo' => 'Gestión de Equipos',
            'equipos' => $equipos
        ]);
    }

    public function crear()
    {
        $this->restrictTo(['admin']);
        
        $this->render('equipos/formulario', [
            'titulo' => 'Registrar Nuevo Equipo',
            'equipo' => null,
            'departamentos' => $this->departamentoModel->findAll(),
            'empleados' => $this->empleadoModel->findAll(),
            'estados_equipo' => ['nuevo', 'usado', 'en_uso', 'fuera_de_servicio', 'en_reparacion', 'disponible'],
            'tipos_equipo' => ['computadora', 'impresora', 'escaner', 'servidor', 'monitor', 'teclado', 'raton', 'otro']
        ]);
    }

    public function editar(int $id)
    {
        $this->restrictTo(['admin']);
        $equipo = $this->equipoModel->findById($id);
        if (!$equipo) {
            $this->setFlashMessage('error', 'Equipo no encontrado.');
            header('Location: ' . BASE_URL . 'equipos');
            exit;
        }
        
        $this->render('equipos/formulario', [
            'titulo' => "Editar Equipo: {$equipo->codigo_inventario}",
            'equipo' => $equipo,
            'departamentos' => $this->departamentoModel->findAll(),
            'empleados' => $this->empleadoModel->findAll(),
            'estados_equipo' => ['nuevo', 'usado', 'en_uso', 'fuera_de_servicio', 'en_reparacion', 'disponible'],
            'tipos_equipo' => ['computadora', 'impresora', 'escaner', 'servidor', 'monitor', 'teclado', 'raton', 'otro']
        ]);
    }

    public function guardar()
    {
        $this->restrictTo(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'equipos');
            exit;
        }

        $validator = new Validator($_POST);
        $id = $validator->getInt('id');
        $es_edicion = !empty($id);

        $validator->check('codigo_inventario', 'required');
        $validator->check('numero_serie', 'required');
        $validator->check('tipo', 'required');
        $validator->check('marca', 'required');
        $validator->check('modelo', 'required');
        $validator->check('estado', 'required');

        if ($validator->fails()) {
            $this->setFlashMessage('error', $validator->getErrors()[array_key_first($validator->getErrors())]);
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        $tipo = $validator->get('tipo');
        if ($tipo === 'otro' && !empty($_POST['tipo_otro'])) {
            $tipo = trim($_POST['tipo_otro']);
        }

        $datos = [
            'codigo_inventario' => $validator->get('codigo_inventario'),
            'numero_serie'      => $validator->get('numero_serie'),
            'tipo'              => $tipo,
            'marca'             => $validator->get('marca'),
            'modelo'            => $validator->get('modelo'),
            'procesador'        => $validator->get('procesador'),
            'memoria_ram'       => $validator->get('memoria_ram'),
            'almacenamiento'    => $validator->get('almacenamiento'),
            'sistema_operativo' => $validator->get('sistema_operativo'),
            'direccion_ip'      => $validator->get('direccion_ip'),
            'driver'            => $validator->get('driver'),
            'toner'             => $validator->get('toner'),
            'departamento_id'   => $validator->getInt('departamento_id') ?: null,
            'empleado_id'       => $validator->getInt('empleado_id') ?: null,
            'ubicacion_fisica'  => $validator->get('ubicacion_fisica'),
            'estado'            => $validator->get('estado'),
            'fecha_compra'      => $validator->get('fecha_compra') ?: null,
            'proveedor'         => $validator->get('proveedor'),
            'proveedor_rif'     => $validator->get('proveedor_rif'),
            'garantia'          => $validator->get('garantia') ?: null,
            'valor_compra'      => $validator->get('valor_compra') ?: null,
        ];

        $fue_asignado = false;
        if (!empty($datos['departamento_id']) || !empty($datos['empleado_id'])) {
            if (!$es_edicion || $datos['estado'] === 'disponible') {
                $datos['estado'] = 'en_uso';
            }
            $fue_asignado = true;
        }

        try {
            if ($es_edicion) {
                if ($this->equipoModel->update($id, $datos)) {
                    $this->setFlashMessage('success', "Equipo #{$id} actualizado.");
                    $this->logBitacora("Actualizó el equipo (Código: {$datos['codigo_inventario']})", 'equipo', $id);
                }
            } else {
                if ($newId = $this->equipoModel->create($datos)) {
                    $this->setFlashMessage('success', 'Equipo creado exitosamente.');
                    $this->logBitacora("Creó el equipo (Código: {$datos['codigo_inventario']})", 'equipo', $newId);
                }
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $this->setFlashMessage('error', 'Error: El código o número de serie ya está registrado.');
            } else {
                $this->setFlashMessage('error', 'Error de BD: ' . $e->getMessage());
            }
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        
        if (!empty($_POST['redirect_url']) && strpos($_POST['redirect_url'], BASE_URL) === 0) {
            header("Location: " . $_POST['redirect_url']);
        } elseif ($fue_asignado || $es_edicion) {
            header("Location: " . BASE_URL . "equipos");
        } else {
            header("Location: " . BASE_URL . "inventario");
        }
        exit;
    }

    public function eliminar(int $id)
    {
        $this->restrictTo(['admin']);
        $equipo = $this->equipoModel->findById($id);
        
        // Setup default response
        $success = false;
        $message = '';
        
        if (!$equipo) {
            $message = "Error: Equipo no encontrado.";
        } else {
            if ($this->equipoModel->delete($id)) {
                $success = true;
                $message = "Equipo eliminado correctamente.";
                $this->logBitacora("Eliminó el equipo (Código: {$equipo->codigo_inventario})", 'equipo', $id);
            } else {
                $message = "Error: No se pudo eliminar el equipo.";
            }
        }

        // Check for AJAX/JSON request
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
                  || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success, 'message' => $message]);
            exit;
        }

        // Fallback for standard request
        $this->setFlashMessage($success ? 'success' : 'error', $message);
        header('Location: ' . BASE_URL . 'inventario');
        exit;
    }

    public function ver(int $id)
    {
        $equipo = $this->equipoModel->findById($id);
        if (!$equipo) {
            $this->setFlashMessage('error', 'Equipo no encontrado.');
            header('Location: ' . BASE_URL . 'equipos');
            exit;
        }

        $departamento = $equipo->departamento_id ? $this->departamentoModel->findById($equipo->departamento_id) : null;
        $empleado = $equipo->empleado_id ? $this->empleadoModel->findById($equipo->empleado_id) : null;
        
        $this->render('equipos/ver', [
            'titulo' => "Detalle del Equipo: {$equipo->codigo_inventario}",
            'equipo' => $equipo,
            'departamento' => $departamento,
            'empleado' => $empleado
        ]);
    }

    public function apiBuscar()
    {
        if (!isset($_GET['q'])) {
            $this->jsonResponse(['error' => 'Query parameter "q" required'], 400);
        }

        $query = trim($_GET['q']);
        
        // Determinar si hay restricción de departamento
        $departamentoIdRestrict = null;
        if (($_SESSION['rol'] === 'tecnico' || $_SESSION['rol'] === 'consultor')) {
            $departamentoIdRestrict = $_SESSION['departamento_id'] ?? 0;
        }

        // Usar el servicio para la búsqueda
        $resultado = $this->equipoService->buscarEquipos($query, $departamentoIdRestrict);
        
        $this->jsonResponse($resultado);
    }

    public function historial(int $id)
    {
        $equipo = $this->equipoModel->findById($id);
        if (!$equipo) {
            $this->setFlashMessage('error', 'Equipo no encontrado.');
            header('Location: ' . BASE_URL . 'equipos');
            exit;
        }

        $bitacoraModel = new \App\Models\Bitacora();
        $historial = $bitacoraModel->findByEntity('equipo', $id);

        $this->render('equipos/historial', [
            'titulo' => "Historial de Cambios: {$equipo->codigo_inventario}",
            'equipo' => $equipo,
            'historial' => $historial
        ]);
    }

    public function imprimir(int $id)
    {
        $equipo = $this->equipoModel->findById($id);
        if (!$equipo) {
            $this->setFlashMessage('error', 'Equipo no encontrado.');
            header('Location: ' . BASE_URL . 'equipos');
            exit;
        }

        $departamento = $equipo->departamento_id ? $this->departamentoModel->findById($equipo->departamento_id) : null;
        $empleado = $equipo->empleado_id ? $this->empleadoModel->findById($equipo->empleado_id) : null;

        $this->render('equipos/imprimir', [
            'titulo' => "Ficha del Equipo: {$equipo->codigo_inventario}",
            'equipo' => $equipo,
            'departamento' => $departamento,
            'empleado' => $empleado
        ], 'blank');
    }

    public function duplicar(int $id)
    {
        $this->restrictTo(['admin']);
        
        try {
            $resultado = $this->equipoService->duplicarEquipo($id);
            
            if ($resultado) {
                $this->setFlashMessage('success', "Equipo duplicado exitosamente. Nuevo código: {$resultado['codigo']}");
                $this->logBitacora("Duplicó el equipo ID #$id al nuevo ID #{$resultado['id']}", 'equipo', $resultado['id']);
                header('Location: ' . BASE_URL . 'equipos/editar/' . $resultado['id']);
                exit;
            } else {
                $this->setFlashMessage('error', 'Equipo no encontrado.');
            }
        } catch (PDOException $e) {
            $this->setFlashMessage('error', 'Error al duplicar: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . 'equipos/ver/' . $id);
        exit;
    }

    /**
     * Bulk delete equipment via AJAX
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

        foreach ($ids as $id) {
            $equipo = $this->equipoModel->findById((int)$id);
            if ($equipo && $this->equipoModel->delete((int)$id)) {
                $deletedCount++;
                $this->logBitacora("Eliminó el equipo (Código: {$equipo->codigo_inventario}) (eliminación masiva)", 'equipo', $id);
            }
        }

        if ($deletedCount > 0) {
            echo json_encode([
                'success' => true,
                'message' => "Se eliminaron {$deletedCount} equipo(s) correctamente.",
                'deleted' => $deletedCount
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar ningún equipo']);
        }
        exit;
    }
}
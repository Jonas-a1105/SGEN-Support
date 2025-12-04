<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Equipo;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Mantenimiento;
use App\Models\Soporte;
use PDOException;

class EquiposController extends Controller
{
    private $equipoModel;
    private $departamentoModel;
    private $empleadoModel;
    private $mantenimientoModel;
    private $soporteModel;

    public function __construct()
    {
        parent::__construct();
        $this->restrictTo(['admin', 'tecnico', 'consultor']);
        
        $this->equipoModel = new Equipo();
        $this->departamentoModel = new Departamento();
        $this->empleadoModel = new Empleado();
        $this->mantenimientoModel = new Mantenimiento();
        $this->soporteModel = new Soporte();
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
            // Solo cambiar a 'en_uso' si se está creando o si el estado seleccionado es 'disponible'
            // Esto permite poner un equipo asignado en 'en_reparacion' o 'fuera_de_servicio'
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
        
        if ($fue_asignado || $es_edicion) {
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
        if (!$equipo) {
            $this->setFlashMessage('error', "Error: Equipo no encontrado.");
            header('Location: ' . BASE_URL . 'equipos');
            exit;
        }

        if ($this->equipoModel->delete($id)) {
            $this->setFlashMessage('success', "Equipo eliminado correctamente.");
            $this->logBitacora("Eliminó el equipo (Código: {$equipo->codigo_inventario})", 'equipo', $id);
        } else {
            $this->setFlashMessage('error', "Error: No se pudo eliminar el equipo.");
        }
        header('Location: ' . BASE_URL . 'inventario');
        exit;
    }
    public function ver(int $id)
    {
        // Allow access to admin, tecnico, consultor
        // (Restriction is already in constructor, but we might want specific logic)
        
        $equipo = $this->equipoModel->findById($id);
        if (!$equipo) {
            $this->setFlashMessage('error', 'Equipo no encontrado.');
            header('Location: ' . BASE_URL . 'equipos');
            exit;
        }

        // Load related data
        $departamento = $equipo->departamento_id ? $this->departamentoModel->findById($equipo->departamento_id) : null;
        $empleado = $equipo->empleado_id ? $this->empleadoModel->findById($equipo->empleado_id) : null;
        
        // Load history/maintenance/support if needed, or just basic details
        // For now, let's assume we just need to render the view
        
        $this->render('equipos/ver', [
            'titulo' => "Detalle del Equipo: {$equipo->codigo_inventario}",
            'equipo' => $equipo,
            'departamento' => $departamento,
            'empleado' => $empleado
        ]);
    }
    public function apiBuscar()
    {
        // Allow access to admin, tecnico, consultor
        // (Restriction is already in constructor)
        
        if (!isset($_GET['q'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Query parameter "q" required']);
            exit;
        }

        $query = trim($_GET['q']);
        
        // First, try to find by serial or codigo (existing functionality)
        $equipo = $this->equipoModel->findBySerialOrCodigo($query);

        if ($equipo) {
            // Check if user has permission to see this equipment (Consultor/Tecnico restriction)
            if (($_SESSION['rol'] === 'tecnico' || $_SESSION['rol'] === 'consultor')) {
                $userDeptId = $_SESSION['departamento_id'] ?? 0;
                if ($equipo->departamento_id != $userDeptId) {
                    // Found but not in their department
                    echo json_encode(['found' => false, 'error' => 'Equipo no pertenece a su departamento']);
                    exit;
                }
            }

            header('Content-Type: application/json');
            echo json_encode([
                'found' => true,
                'id' => $equipo->id,
                'tipo' => $equipo->tipo,
                'marca' => $equipo->marca,
                'modelo' => $equipo->modelo,
                'serial' => $equipo->numero_serie,
                'departamento_id' => $equipo->departamento_id,
                'departamento_nombre' => $equipo->departamento_nombre
            ]);
            exit;
        }
        
        // If not found by serial/codigo, try searching by employee cedula
        $equipos = $this->equipoModel->findAllByEmpleadoCedula($query);
        
        if (!empty($equipos)) {
            // Filter by department if user is tecnico/consultor
            if (($_SESSION['rol'] === 'tecnico' || $_SESSION['rol'] === 'consultor')) {
                $userDeptId = $_SESSION['departamento_id'] ?? 0;
                $equipos = array_filter($equipos, function($eq) use ($userDeptId) {
                    return $eq->departamento_id == $userDeptId;
                });
                
                // Reindex array after filtering
                $equipos = array_values($equipos);
            }
            
            if (empty($equipos)) {
                echo json_encode(['found' => false, 'error' => 'No se encontraron equipos asignados a esa cédula en su departamento']);
                exit;
            }
            
            header('Content-Type: application/json');
            
            // If only one equipment, return it directly (backward compatible)
            if (count($equipos) === 1) {
                $eq = $equipos[0];
                echo json_encode([
                    'found' => true,
                    'id' => $eq->id,
                    'tipo' => $eq->tipo,
                    'marca' => $eq->marca,
                    'modelo' => $eq->modelo,
                    'serial' => $eq->numero_serie,
                    'departamento_id' => $eq->departamento_id,
                    'departamento_nombre' => $eq->departamento_nombre
                ]);
            } else {
                // Multiple equipment found, return array
                $equiposData = array_map(function($eq) {
                    return [
                        'id' => $eq->id,
                        'tipo' => $eq->tipo,
                        'marca' => $eq->marca,
                        'modelo' => $eq->modelo,
                        'serial' => $eq->numero_serie,
                        'codigo_inventario' => $eq->codigo_inventario,
                        'departamento_id' => $eq->departamento_id,
                        'departamento_nombre' => $eq->departamento_nombre
                    ];
                }, $equipos);
                
                echo json_encode([
                    'found' => true,
                    'multiple' => true,
                    'equipos' => $equiposData
                ]);
            }
            exit;
        }
        
        // Not found by any method
        echo json_encode(['found' => false]);
        exit;
    }


}

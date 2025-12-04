<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Inventario;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Equipo;
use App\Core\Validator;

class InventarioController extends Controller {
    private $inventarioModel;
    private $departamentoModel;
    private $empleadoModel;
    private $equipoModel;

    public function __construct() {
        parent::__construct();
        $this->inventarioModel = new Inventario();
        $this->departamentoModel = new Departamento();
        $this->empleadoModel = new Empleado();
        $this->equipoModel = new Equipo();
    }

    public function index() {
        // Search
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';

        // Pagination for Items
        $pageItems = isset($_GET['page_items']) ? (int)$_GET['page_items'] : 1;
        $limitItems = 10;
        $offsetItems = ($pageItems - 1) * $limitItems;
        
        $items = $this->inventarioModel->obtenerTodosPaginated($limitItems, $offsetItems, $search);
        $totalItemsCount = $this->inventarioModel->countAll($search);
        $totalPagesItems = ceil($totalItemsCount / $limitItems);

        // Pagination for Unassigned Equipment
        $pageEquipos = isset($_GET['page_equipos']) ? (int)$_GET['page_equipos'] : 1;
        $limitEquipos = 10;
        $offsetEquipos = ($pageEquipos - 1) * $limitEquipos;

        $equiposSinAsignar = $this->equipoModel->findUnassignedPaginated($limitEquipos, $offsetEquipos, $search);
        $totalEquiposCount = $this->equipoModel->countUnassigned($search);
        $totalPagesEquipos = ceil($totalEquiposCount / $limitEquipos);
        
        // Datos necesarios para el formulario de equipos (modal)
        $departamentos = $this->departamentoModel->findAll();
        $empleados = $this->empleadoModel->findAll();
        
        // Arrays necesarios para el formulario de equipos
        $estados_equipo = ['nuevo', 'usado', 'en_uso', 'fuera_de_servicio', 'en_reparacion', 'disponible', 'en_reserva'];
        $tipos_equipo = ['computadora', 'impresora', 'escaner', 'servidor', 'monitor', 'teclado', 'raton', 'otro'];
        
        $this->render('inventario/lista', [
            'items' => $items, 
            'equiposSinAsignar' => $equiposSinAsignar,
            'titulo' => 'Inventario General',
            'departamentos' => $departamentos,
            'empleados' => $empleados,
            'estados_equipo' => $estados_equipo,
            'tipos_equipo' => $tipos_equipo,
            // Pagination Data
            'pageItems' => $pageItems,
            'totalPagesItems' => $totalPagesItems,
            'pageEquipos' => $pageEquipos,
            'totalPagesEquipos' => $totalPagesEquipos,
            'search' => $search
        ]);
    }

    public function crear() {
        $this->restrictTo(['admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'codigo' => $_POST['codigo'] ?? '',
                'nombre' => $_POST['nombre'] ?? '',
                'categoria' => $_POST['categoria'] ?? '',
                'descripcion' => $_POST['descripcion'] ?? '',
                'marca' => $_POST['marca'] ?? '',
                'modelo' => $_POST['modelo'] ?? '',
                'unidad_medida' => $_POST['unidad_medida'] ?? '',
                'stock_actual' => $_POST['stock_inicial'] ?? 0,
                'stock_minimo' => $_POST['stock_minimo'] ?? 0,
                'ubicacion' => 'Almacén Central',
                'fecha_compra' => !empty($_POST['fecha_compra']) ? $_POST['fecha_compra'] : null,
                'proveedor' => $_POST['proveedor'] ?? null,
                'proveedor_rif' => $_POST['proveedor_rif'] ?? null,
                'garantia_fin' => !empty($_POST['garantia_fin']) ? $_POST['garantia_fin'] : null,
                'valor_compra' => !empty($_POST['valor_compra']) ? $_POST['valor_compra'] : 0.00
            ];
            
            if ($this->inventarioModel->registrarItem($datos)) {
                $this->setFlashMessage('success', 'Ítem creado exitosamente.');
                $this->logBitacora("Registró nuevo ítem '{$datos['nombre']}'", 'inventario', null);
                header('Location: ' . BASE_URL . 'inventario');
                exit;
            }
        }
        $this->render('inventario/formulario', ['titulo' => 'Registrar Nuevo Ítem']);
    }

    public function movimiento() {
        $this->restrictTo(['admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['item_id'];
            $cantidad = $_POST['cantidad'];
            $tipo = $_POST['tipo'];
            $motivo = $_POST['motivo'];
            $usuario = $_SESSION['user_id'];

            try {
                $this->inventarioModel->actualizarStock($id, $cantidad, $tipo, $usuario, $motivo);
                $this->setFlashMessage('success', 'Movimiento registrado exitosamente.');
                $this->logBitacora("Registró movimiento {$tipo} de {$cantidad} unidades para ítem #{$id}", 'inventario', $id);
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Error al registrar movimiento: ' . $e->getMessage());
            }
            header('Location: ' . BASE_URL . 'inventario');
            exit;
        }
    }

    public function ver(int $id) {
        $item = $this->inventarioModel->findById($id);
        if (!$item) {
            $this->setFlashMessage('error', 'Ítem no encontrado.');
            header('Location: ' . BASE_URL . 'inventario');
            exit;
        }

        $movimientos = $this->inventarioModel->obtenerMovimientos($id);

        $this->render('inventario/ver', [
            'titulo' => 'Detalle del Artículo: ' . $item->nombre,
            'item' => $item,
            'movimientos' => $movimientos
        ]);
    }

    public function distribucion(int $itemId) {
        $item = $this->inventarioModel->findById($itemId);
        if (!$item) {
            $this->setFlashMessage('error', 'Ítem no encontrado.');
            header('Location: ' . BASE_URL . 'inventario');
            exit;
        }

        $stockDetallado = $this->inventarioModel->obtenerStockDetallado($itemId);
        $departamentos = $this->departamentoModel->findAll();

        $this->render('inventario/distribucion', [
            'titulo' => 'Distribución de Stock: ' . $item->nombre,
            'item' => $item,
            'stockDetallado' => $stockDetallado,
            'departamentos' => $departamentos
        ]);
    }

    public function transferir() {
        $this->restrictTo(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'inventario');
            exit;
        }

        $itemId = $_POST['item_id'];
        $origenId = empty($_POST['origen_id']) ? null : $_POST['origen_id'];
        $destinoId = empty($_POST['destino_id']) ? null : $_POST['destino_id'];
        $cantidad = (int)$_POST['cantidad'];
        $motivo = $_POST['motivo'];
        $usuarioId = $_SESSION['user_id'];

        if ($origenId == $destinoId) {
            $this->setFlashMessage('error', 'El origen y el destino no pueden ser iguales.');
            header('Location: ' . BASE_URL . 'inventario/distribucion/' . $itemId);
            exit;
        }

        try {
            $this->inventarioModel->transferirStock($itemId, $origenId, $destinoId, $cantidad, $usuarioId, $motivo);
            $this->setFlashMessage('success', 'Transferencia realizada exitosamente.');
            $this->logBitacora("Transfirió {$cantidad} unidades del ítem #{$itemId}", 'inventario', $itemId);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Error en la transferencia: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . 'inventario/distribucion/' . $itemId);
        exit;
    }
    
    public function por_departamento($id = null)
    {
        $this->restrictTo(['admin', 'tecnico']);
        
        if (!$id) {
            if ($_SESSION['rol'] === 'admin') {
                $departamentos = (new \App\Models\Departamento())->findAll();
                $this->render('inventario/selector_departamento', [
                    'titulo' => 'Inventario por Departamento',
                    'departamentos' => $departamentos
                ]);
                return;
            } else {
                $deptId = $_SESSION['departamento_id'];
                if ($deptId) {
                    header('Location: ' . BASE_URL . 'inventario/departamento/' . $deptId);
                    exit;
                } else {
                    $this->setFlashMessage('error', 'No tienes un departamento asignado.');
                    header('Location: ' . BASE_URL . 'inventario');
                    exit;
                }
            }
        }

        if ($_SESSION['rol'] === 'tecnico' && $id != $_SESSION['departamento_id']) {
            $this->setFlashMessage('error', 'No tienes permiso para ver el inventario de otros departamentos.');
            header('Location: ' . BASE_URL . 'inventario/departamento/' . $_SESSION['departamento_id']);
            exit;
        }

        $departamento = (new \App\Models\Departamento())->findById($id);
        if (!$departamento) {
            $this->setFlashMessage('error', 'Departamento no encontrado.');
            header('Location: ' . BASE_URL . 'inventario');
            exit;
        }

        $items = $this->inventarioModel->obtenerItemsPorDepartamento($id);

        $this->render('inventario/por_departamento', [
            'titulo' => 'Inventario: ' . $departamento->nombre,
            'items' => $items,
            'departamento' => $departamento
        ]);
    }

    public function historial()
    {
        $this->restrictTo(['admin']);
        $movimientos = $this->inventarioModel->obtenerTodosMovimientos(100);
        $this->render('inventario/historial', [
            'titulo' => 'Historial de Movimientos de Inventario',
            'movimientos' => $movimientos
        ]);
    }

    public function baja() {
        $this->restrictTo(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'inventario');
            exit;
        }

        $itemId = $_POST['item_id'];
        $cantidad = (int)$_POST['cantidad'];
        $motivo = $_POST['motivo'];
        $usuarioId = $_SESSION['user_id'];
        
        // Check for complete deletion flag
        $eliminarCompleto = isset($_POST['eliminar_completo']) && $_POST['eliminar_completo'] == '1';

        if (!$eliminarCompleto && $cantidad <= 0) {
            $this->setFlashMessage('error', 'La cantidad debe ser mayor a 0.');
            header('Location: ' . BASE_URL . 'inventario');
            exit;
        }

        try {
            if ($eliminarCompleto) {
                if ($this->inventarioModel->eliminarItem($itemId)) {
                    $this->setFlashMessage('success', 'Artículo eliminado completamente del inventario.');
                    $this->logBitacora("Eliminó el artículo #{$itemId} del inventario", 'inventario', $itemId);
                } else {
                    throw new \Exception("No se pudo eliminar el artículo.");
                }
            } else {
                $this->inventarioModel->registrarBaja($itemId, $cantidad, $motivo, $usuarioId);
                $this->setFlashMessage('success', 'Baja registrada exitosamente.');
                $this->logBitacora("Registró baja de {$cantidad} unidades del ítem #{$itemId}", 'inventario', $itemId);
            }
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Error: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . 'inventario');
        exit;
    }
}
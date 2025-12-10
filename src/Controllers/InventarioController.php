<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Inventario;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Equipo;
use App\Services\InventarioService;
use Exception;

class InventarioController extends Controller {
    private $inventarioModel;
    private $departamentoModel;
    private $empleadoModel;
    private $equipoModel;
    private $inventarioService;

    public function __construct() {
        parent::__construct();
        $this->inventarioModel = new Inventario();
        $this->departamentoModel = new Departamento();
        $this->empleadoModel = new Empleado();
        $this->equipoModel = new Equipo();
        $this->inventarioService = new InventarioService();
    }

    public function index() {
        // Search
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';

        // Pagination Preference
        $cookiePerPage = isset($_COOKIE['sgen_pagination_per_page']) ? (int)$_COOKIE['sgen_pagination_per_page'] : 10;
        $perPage = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : $cookiePerPage;

        // Pagination for Items
        $pageItems = isset($_GET['page_items']) ? (int)$_GET['page_items'] : 1;
        $limitItems = $perPage;
        $offsetItems = ($pageItems - 1) * $limitItems;
        
        $items = $this->inventarioModel->obtenerTodosPaginated($limitItems, $offsetItems, $search);
        $totalItemsCount = $this->inventarioModel->countAll($search);
        $totalPagesItems = ceil($totalItemsCount / $limitItems);

        // Pagination for Unassigned Equipment
        $pageEquipos = isset($_GET['page_equipos']) ? (int)$_GET['page_equipos'] : 1;
        $limitEquipos = $perPage;
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
            'totalPagesItems' => $totalPagesItems,
            'pageItems' => $pageItems,
            'totalPagesEquipos' => $totalPagesEquipos,
            'pageEquipos' => $pageEquipos,
            'search' => $search,
            'totalItemsCount' => $totalItemsCount,
            'totalEquiposCount' => $totalEquiposCount,
            'perPage' => $perPage
        ]);
    }

    public function crear() {
        $this->restrictTo(['admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoria = $_POST['categoria'] ?? '';
            if ($categoria === 'Otros' && !empty($_POST['categoria_otra'])) {
                $categoria = trim($_POST['categoria_otra']);
            }

            $datos = [
                'codigo' => $_POST['codigo'] ?? '',
                'nombre' => $_POST['nombre'] ?? '',
                'categoria' => $categoria,
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
            
            try {
                $itemId = $this->inventarioService->registrarItem($datos, $_SESSION['user_id']);
                if ($itemId > 0) {
                    $this->setFlashMessage('success', 'Ítem creado exitosamente.');
                    header('Location: ' . BASE_URL . 'inventario');
                    exit;
                }
            } catch (Exception $e) {
                $this->setFlashMessage('error', 'Error al crear: ' . $e->getMessage());
            }
        }
        $this->render('inventario/formulario', ['titulo' => 'Registrar Nuevo Ítem']);
    }

    public function movimiento() {
        $this->restrictTo(['admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['item_id'];
            $cantidad = (int)$_POST['cantidad'];
            $tipo = $_POST['tipo'];
            $motivo = $_POST['motivo'];
            $usuarioId = $_SESSION['user_id'];

            try {
                $this->inventarioService->registrarMovimiento($id, $cantidad, $tipo, $motivo, $usuarioId);
                $this->setFlashMessage('success', 'Movimiento registrado exitosamente.');
            } catch (Exception $e) {
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

    public function editar(int $id) {
        $this->restrictTo(['admin']);
        
        $item = $this->inventarioModel->findById($id);
        if (!$item) {
            $this->setFlashMessage('error', 'Ítem no encontrado.');
            header('Location: ' . BASE_URL . 'inventario');
            exit;
        }

        $this->render('inventario/formulario', [
            'titulo' => 'Editar Artículo: ' . $item->nombre,
            'item' => $item,
            'editMode' => true
        ]);
    }

    public function actualizar(int $id) {
        $this->restrictTo(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'inventario');
            exit;
        }

        $item = $this->inventarioModel->findById($id);
        if (!$item) {
            $this->setFlashMessage('error', 'Ítem no encontrado.');
            header('Location: ' . BASE_URL . 'inventario');
            exit;
        }

        $categoria = $_POST['categoria'] ?? $item->categoria;
        if ($categoria === 'Otros' && !empty($_POST['categoria_otra'])) {
            $categoria = trim($_POST['categoria_otra']);
        }

        $datos = [
            'id' => $id,
            'codigo' => $_POST['codigo'] ?? $item->codigo,
            'nombre' => $_POST['nombre'] ?? $item->nombre,
            'categoria' => $categoria,
            'descripcion' => $_POST['descripcion'] ?? $item->descripcion,
            'marca' => $_POST['marca'] ?? $item->marca,
            'modelo' => $_POST['modelo'] ?? $item->modelo,
            'unidad_medida' => $_POST['unidad_medida'] ?? $item->unidad_medida,
            'stock_minimo' => $_POST['stock_minimo'] ?? $item->stock_minimo,
            'ubicacion' => $_POST['ubicacion'] ?? $item->ubicacion,
            'proveedor' => $_POST['proveedor'] ?? $item->proveedor,
            'proveedor_rif' => $_POST['proveedor_rif'] ?? $item->proveedor_rif,
            'valor_compra' => $_POST['valor_compra'] ?? $item->valor_compra,
        ];

        try {
            $this->inventarioService->actualizarItem($id, $datos, $_SESSION['user_id']);
            $this->setFlashMessage('success', 'Artículo actualizado correctamente.');
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error al actualizar: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . 'inventario/ver/' . $id);
        exit;
    }

    public function eliminar(int $id) {
        $this->restrictTo(['admin']);
        
        // This is now handled by the 'baja' method with 'eliminar_completo' flag usually, 
        // but if we want a direct delete route, we can use the service's registrarBaja with flag true
        try {
            $this->inventarioService->registrarBaja($id, 0, 'Eliminación Directa', $_SESSION['user_id'], true);
            $this->setFlashMessage('success', 'Artículo eliminado correctamente.');
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error al eliminar: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . 'inventario');
        exit;
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

        $itemId = (int)$_POST['item_id'];
        $origenId = empty($_POST['origen_id']) ? null : (int)$_POST['origen_id'];
        $destinoId = empty($_POST['destino_id']) ? null : (int)$_POST['destino_id'];
        $cantidad = (int)$_POST['cantidad'];
        $motivo = $_POST['motivo'];
        $usuarioId = $_SESSION['user_id'];

        try {
            $this->inventarioService->transferirStock($itemId, $origenId, $destinoId, $cantidad, $usuarioId, $motivo);
            $this->setFlashMessage('success', 'Transferencia realizada exitosamente.');
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error en la transferencia: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . 'inventario/distribucion/' . $itemId);
        exit;
    }
    


    public function historial_departamento(int $id)
    {
        $this->restrictTo(['admin', 'tecnico']);
        
        if ($_SESSION['rol'] === 'tecnico' && $id != $_SESSION['departamento_id']) {
            $this->setFlashMessage('error', 'No tienes permiso para ver el historial de otros departamentos.');
            header('Location: ' . BASE_URL . 'inventario/departamento/' . $_SESSION['departamento_id']);
            exit;
        }

        $departamento = (new \App\Models\Departamento())->findByIdWithStats($id);
        if (!$departamento) {
            $this->setFlashMessage('error', 'Departamento no encontrado.');
            header('Location: ' . BASE_URL . 'inventario');
            exit;
        }

        $movimientos = $this->inventarioModel->obtenerMovimientosPorDepartamento($id, 50);

        $this->render('inventario/historial_departamento', [
            'titulo' => 'Historial: ' . $departamento->nombre,
            'departamento' => $departamento,
            'movimientos' => $movimientos
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

    public function historial_item(int $id)
    {
        $this->restrictTo(['admin']);
        $item = $this->inventarioModel->findById($id);
        if (!$item) {
            $this->setFlashMessage('error', 'Ítem no encontrado.');
            header('Location: ' . BASE_URL . 'inventario');
            exit;
        }

        // Pagination
        $cookiePerPage = isset($_COOKIE['sgen_pagination_per_page']) ? (int)$_COOKIE['sgen_pagination_per_page'] : 10;
        $perPage = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : $cookiePerPage;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $perPage;

        $movimientos = $this->inventarioModel->obtenerMovimientosPaginated($id, $perPage, $offset);
        $totalItems = $this->inventarioModel->countMovimientos($id);
        $totalPages = ceil($totalItems / $perPage);

        $this->render('inventario/historial_item', [
            'titulo' => 'Historial: ' . $item->nombre,
            'item' => $item,
            'movimientos' => $movimientos,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalItems' => $totalItems
        ]);
    }

    public function baja() {
        $this->restrictTo(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'inventario');
            exit;
        }

        $itemId = (int)$_POST['item_id'];
        $cantidad = (int)$_POST['cantidad'];
        $motivo = $_POST['motivo'];
        $usuarioId = $_SESSION['user_id'];
        $eliminarCompleto = isset($_POST['eliminar_completo']) && $_POST['eliminar_completo'] == '1';

        try {
            $this->inventarioService->registrarBaja($itemId, $cantidad, $motivo, $usuarioId, $eliminarCompleto);
            $this->setFlashMessage('success', $eliminarCompleto ? 'Artículo eliminado completamente.' : 'Baja registrada exitosamente.');
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . 'inventario');
        exit;
    }

    /**
     * Bulk delete inventory items via AJAX
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
            try {
                $this->inventarioService->registrarBaja((int)$id, 0, 'Eliminación Masiva', $_SESSION['user_id'], true);
                $deletedCount++;
            } catch (Exception $e) {
                // Skip failed items
            }
        }

        if ($deletedCount > 0) {
            echo json_encode([
                'success' => true,
                'message' => "Se eliminaron {$deletedCount} artículo(s) correctamente.",
                'deleted' => $deletedCount
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar ningún artículo']);
        }
        exit;
    }
}

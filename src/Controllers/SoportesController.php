<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Soporte;
use App\Models\Equipo;
use App\Models\Departamento;
use App\Models\Usuario;
use App\Models\Inventario;
use App\Models\Empleado;
use App\Models\Categoria;
use App\Models\TicketArchivo;
use App\Models\TicketComentario;
use App\Models\Notificacion;
use App\Services\TicketService;
use App\Services\InventarioService;
use App\Services\FileUploadService;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * SoportesController - Controlador refactorizado
 * Lógica de negocio delegada a TicketService y FileUploadService
 */
class SoportesController extends Controller
{
    private $soporteModel;
    private $equipoModel;
    private $departamentoModel;
    private $usuarioModel;
    private $inventarioModel;
    private $categoriaModel;
    private $ticketArchivoModel;
    private $ticketComentarioModel;
    private $notificacionModel;
    
    // Services
    private $ticketService;
    private $fileUploadService;
    private $inventarioService;

    public function __construct()
    {
        parent::__construct();

        // Models
        $this->soporteModel = new Soporte();
        $this->equipoModel = new Equipo();
        $this->departamentoModel = new Departamento();
        $this->usuarioModel = new Usuario();
        $this->inventarioModel = new Inventario();
        $this->categoriaModel = new Categoria();
        $this->ticketArchivoModel = new TicketArchivo();
        $this->ticketComentarioModel = new TicketComentario();
        $this->notificacionModel = new Notificacion();
        
        // Services
        $this->ticketService = new TicketService();
        $this->fileUploadService = new FileUploadService();
        $this->inventarioService = new InventarioService();
    }

    /**
     * Lista de soportes (visible para todos los roles).
     */
    public function index()
    {
        if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'consultor') {
            $departamentoId = $_SESSION['departamento_id'] ?? 0;
            $soportes = $this->soporteModel->findAllByDepartment($departamentoId);
        } else {
            $soportes = $this->soporteModel->findAllWithDetails();
        }
        
        // Calcular KPIs
        $kpis = ['pendientes' => 0, 'en_proceso' => 0, 'resueltos' => 0, 'alta_prioridad' => 0];
        foreach ($soportes as $soporte) {
            if ($soporte->estado === 'pendiente') $kpis['pendientes']++;
            if ($soporte->estado === 'en_proceso') $kpis['en_proceso']++;
            if ($soporte->estado === 'resuelto') $kpis['resueltos']++;
            if ($soporte->prioridad === 'alta' && $soporte->estado !== 'resuelto') $kpis['alta_prioridad']++;
        }
        
        $categorias = $this->categoriaModel->findAll();

        $this->render('soportes/lista', [
            'soportes' => $soportes,
            'kpis' => $kpis,
            'categorias' => $categorias,
            'titulo' => 'Listado de Soportes'
        ]);
    }

    /**
     * Vista detallada de un ticket.
     */
    public function ver(int $id)
    {
        $soporte = $this->soporteModel->findByIdWithDetails($id);

        if (!$soporte) {
            http_response_code(404);
            die("Error 404: Ticket no encontrado.");
        }

        $consumos = $this->soporteModel->getConsumos($id);
        $equipo = $this->equipoModel->findById($soporte->equipo_id);
        $departamentoId = $equipo->departamento_id ?? null;
        
        $items = $departamentoId ? $this->inventarioModel->obtenerItemsPorDepartamento($departamentoId) : [];
        
        $includeInternos = isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'tecnico']);
        $comentarios = $this->ticketComentarioModel->findByTicketId($id, $includeInternos);
        
        // Cargar archivos adjuntos
        $archivos = $this->ticketArchivoModel->findByTicketId($id);

        $this->render('soportes/detalle', [
            'titulo' => "Detalle de Soporte #{$soporte->id}",
            'soporte' => $soporte,
            'consumos' => $consumos,
            'items' => $items,
            'comentarios' => $comentarios,
            'archivos' => $archivos
        ]);
    }

    /**
     * Formulario de creación de soporte.
     */
    public function crear()
    {
        $rol = $_SESSION['rol'] ?? '';
        if ($rol === 'tecnico' || $rol === 'consultor') {
            $departamentoId = $_SESSION['departamento_id'] ?? 0;
            $equipos = $this->equipoModel->findByDepartamentoId($departamentoId);
            $departamentos = $this->departamentoModel->findById($departamentoId);
            $departamentos = $departamentos ? [$departamentos] : [];
        } else {
            $equipos = $this->equipoModel->findAllWithDetails();
            $departamentos = $this->departamentoModel->findAll();
        }

        $categorias = $this->categoriaModel->findAllActive();

        $this->render('soportes/formulario', [
            'titulo' => 'Crear Nuevo Ticket de Soporte',
            'equipo_list' => $equipos,
            'departamento_list' => $departamentos,
            'categoria_list' => $categorias,
            'soporte' => null
        ]);
    }

    /**
     * Formulario de edición de soporte.
     */
    public function editar(int $id)
    {
        $this->restrictTo(['admin', 'tecnico', 'consultor']);

        $soporte = $this->soporteModel->findByIdWithDetails($id);
        if (!$soporte) {
            die("Ticket de soporte no encontrado.");
        }

        // Validar permisos usando el service
        if (!$this->ticketService->puedeEditar($id, $_SESSION['user_id'], $_SESSION['rol'])) {
            $this->setFlashMessage('error', 'No tienes permiso para editar este ticket.');
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        }

        if ($_SESSION['rol'] === 'tecnico' || $_SESSION['rol'] === 'consultor') {
            $departamentoId = $_SESSION['departamento_id'] ?? 0;
            $equipos = $this->equipoModel->findByDepartamentoId($departamentoId);
            $departamentos = $this->departamentoModel->findById($departamentoId);
            $departamentos = $departamentos ? [$departamentos] : [];
        } else {
            $equipos = $this->equipoModel->findAllWithDetails();
            $departamentos = $this->departamentoModel->findAll();
        }

        $categorias = $this->categoriaModel->findAllActive();

        $this->render('soportes/formulario', [
            'titulo' => "Editar Ticket #{$id}",
            'equipo_list' => $equipos,
            'departamento_list' => $departamentos,
            'categoria_list' => $categorias,
            'soporte' => $soporte
        ]);
    }

    /**
     * Procesa creación/edición de soporte.
     */
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'soportes/crear');
            exit;
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $equipo_id = filter_input(INPUT_POST, 'equipo_id', FILTER_SANITIZE_NUMBER_INT);
        $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS);
        $prioridad = filter_input(INPUT_POST, 'prioridad', FILTER_SANITIZE_SPECIAL_CHARS);
        $categoria_id = filter_input(INPUT_POST, 'categoria_id', FILTER_SANITIZE_NUMBER_INT);

        if (empty($descripcion) || empty($equipo_id)) {
            $this->setFlashMessage('error', 'Error: Faltan datos obligatorios.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        // Validar equipo-departamento para técnicos/consultores
        if (in_array($_SESSION['rol'], ['tecnico', 'consultor'])) {
            if (!$this->ticketService->validarEquipoDepartamento($equipo_id, $_SESSION['departamento_id'] ?? null)) {
                $this->setFlashMessage('error', 'No tiene permisos para crear tickets para equipos fuera de su departamento.');
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
        }

        $datos = [
            'equipo_id' => $equipo_id,
            'descripcion' => $descripcion,
            'prioridad' => $prioridad ?? 'media',
            'categoria_id' => $categoria_id ?: null,
        ];

        if ($id) {
            // Actualización
            $this->restrictTo(['admin', 'consultor', 'tecnico']);

            if (!$this->ticketService->puedeEditar($id, $_SESSION['user_id'], $_SESSION['rol'])) {
                $this->setFlashMessage('error', 'No tienes permiso para editar este ticket.');
                header('Location: ' . BASE_URL . 'soportes');
                exit;
            }

            if ($this->ticketService->actualizarTicket($id, $datos)) {
                $this->setFlashMessage('success', "Ticket #{$id} actualizado correctamente.");
                $this->logBitacora("Actualizó ticket #{$id}", 'soporte', $id);
            }
            $redirect_id = $id;
        } else {
            // Creación
            $redirect_id = $this->ticketService->crearTicket($datos, $_SESSION['user_id']);
            
            if ($redirect_id) {
                $this->setFlashMessage('success', 'Nuevo ticket de soporte creado exitosamente.');
                $this->logBitacora("Creó ticket #{$redirect_id}", 'soporte', $redirect_id);
                $this->ticketService->notificarAdmins(
                    "{$_SESSION['username']} creó un nuevo ticket #{$redirect_id}",
                    "/soportes/ver/{$redirect_id}"
                );
            }
        }

        header("Location: " . BASE_URL . "soportes/ver/{$redirect_id}");
        exit;
    }

    /**
     * Formulario para asignar técnico.
     */
    public function asignar(int $id)
    {
        $this->restrictTo(['admin', 'tecnico']);

        $soporte = $this->soporteModel->findByIdWithDetails($id);
        if (!$soporte || $soporte->estado !== 'pendiente') {
            die("Error: Ticket no encontrado o ya no está pendiente.");
        }

        $tecnicos = $this->usuarioModel->findAllTechnicians();

        $this->render('soportes/asignar_form', [
            'titulo' => "Asignar Técnico a Soporte #{$id}",
            'soporte' => $soporte,
            'tecnicos' => $tecnicos
        ]);
    }

    /**
     * Procesa asignación de técnico.
     */
    public function procesar_asignacion()
    {
        $this->restrictTo(['admin', 'tecnico']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        }

        $soporte_id = filter_input(INPUT_POST, 'soporte_id', FILTER_SANITIZE_NUMBER_INT);
        $empleado_id = filter_input(INPUT_POST, 'empleado_id', FILTER_SANITIZE_NUMBER_INT);

        if (empty($soporte_id) || empty($empleado_id)) {
            die("Error: Faltan IDs para la asignación.");
        }

        if ($this->ticketService->asignarTecnico($soporte_id, $empleado_id)) {
            $this->logBitacora("Asignó ticket #{$soporte_id} a empleado #{$empleado_id}", 'soporte', $soporte_id);
            header("Location: " . BASE_URL . "soportes/ver/{$soporte_id}");
            exit;
        } else {
            die("Error: No se pudo asignar el técnico.");
        }
    }

    /**
     * Marca un ticket como resuelto.
     */
    public function resolver(int $id)
    {
        $this->restrictTo(['admin', 'tecnico']);

        $resultado = $this->ticketService->resolverTicket($id);

        if ($resultado['success']) {
            $this->setFlashMessage('success', "¡Ticket #{$id} marcado como RESUELTO!");
            $this->logBitacora("Resolvió ticket #{$id}", 'soporte', $id);
            $this->ticketService->notificarAdmins(
                "{$_SESSION['username']} resolvió el ticket #{$id}",
                "/soportes/ver/{$id}"
            );
        } else {
            $this->setFlashMessage('error', $resultado['error']);
        }

        header("Location: " . BASE_URL . "soportes/ver/{$id}");
        exit;
    }

    /**
     * Pone un ticket en espera.
     */
    public function marcar_espera(int $id)
    {
        $this->restrictTo(['admin', 'tecnico']);

        $resultado = $this->ticketService->ponerEnEspera($id);

        if ($resultado['success']) {
            $this->setFlashMessage('success', "Ticket #{$id} puesto EN ESPERA.");
            $this->logBitacora("Puso en espera ticket #{$id}", 'soporte', $id);
        } else {
            $this->setFlashMessage('error', $resultado['error']);
        }

        header("Location: " . BASE_URL . "soportes/ver/{$id}");
        exit;
    }

    /**
     * Reanuda un ticket en espera.
     */
    public function reanudar(int $id)
    {
        $this->restrictTo(['admin', 'tecnico']);

        $resultado = $this->ticketService->reanudarTicket($id);

        if ($resultado['success']) {
            $this->setFlashMessage('success', "Ticket #{$id} REANUDADO (En Proceso).");
            $this->logBitacora("Reanudó ticket #{$id}", 'soporte', $id);
        } else {
            $this->setFlashMessage('error', $resultado['error']);
        }

        header("Location: " . BASE_URL . "soportes/ver/{$id}");
        exit;
    }

    /**
     * Agrega un consumo de inventario a un ticket
     */
    public function agregar_consumo()
    {
        $this->restrictTo(['admin', 'tecnico']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        }

        $soporteId = $_POST['soporte_id'] ?? null;
        $itemId = $_POST['item_id'] ?? null;
        $cantidad = (int)($_POST['cantidad'] ?? 0);
        $usuarioId = $_SESSION['user_id'] ?? null;

        if (!$soporteId || !$itemId || $cantidad <= 0) {
            $this->setFlashMessage('error', 'Datos inválidos.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'soportes'));
            exit;
        }

        $soporte = $this->soporteModel->findByIdWithDetails($soporteId);
        $equipo = $this->equipoModel->findById($soporte->equipo_id);
        $departamentoId = $equipo->departamento_id ?? null;

        if (!$departamentoId) {
            $this->setFlashMessage('error', 'El equipo no tiene un departamento asignado.');
            header('Location: ' . BASE_URL . "soportes/ver/{$soporteId}");
            exit;
        }

        try {
            $this->inventarioService->consumirEnTicket($itemId, $soporteId, $cantidad, $usuarioId, $departamentoId);
            $this->setFlashMessage('success', 'Consumo registrado exitosamente.');
            $this->logBitacora("Agregó consumo de inventario al ticket #{$soporteId}", 'soporte', $soporteId);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Error: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . "soportes/ver/{$soporteId}#info");
        exit;
    }



    /**
     * Actualiza la fecha de cierre de un ticket (solo admin).
     */
    public function actualizar_fecha_cierre()
    {
        $this->restrictTo(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        }

        $soporteId = filter_input(INPUT_POST, 'soporte_id', FILTER_SANITIZE_NUMBER_INT);
        $nuevaFechaCierre = $_POST['nueva_fecha_cierre'] ?? '';

        if (!$soporteId || empty($nuevaFechaCierre)) {
            $this->setFlashMessage('error', 'Datos inválidos.');
            header('Location: ' . BASE_URL . "soportes/ver/{$soporteId}");
            exit;
        }

        $resultado = $this->ticketService->actualizarFechaCierre($soporteId, $nuevaFechaCierre);

        if ($resultado['success']) {
            $this->setFlashMessage('success', 'Fecha de cierre actualizada y tiempo de atención recalculado.');
            $this->logBitacora("Actualizó fecha de cierre del Ticket #{$soporteId} a {$resultado['fecha']}", 'soporte', $soporteId);
        } else {
            $this->setFlashMessage('error', $resultado['error'] ?? 'Error al actualizar la fecha de cierre.');
        }

        header("Location: " . BASE_URL . "soportes/ver/{$soporteId}");
        exit;
    }

    /**
     * Sube un archivo adjunto a un ticket.
     */
    public function subir_archivo()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        $ticketId = filter_input(INPUT_POST, 'soporte_id', FILTER_SANITIZE_NUMBER_INT);
        
        if (!$ticketId || empty($_FILES['archivo'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos']);
            exit;
        }

        $resultado = $this->fileUploadService->subirArchivo(
            $ticketId, 
            $_FILES['archivo'], 
            $_SESSION['user_id'] ?? null
        );

        if ($resultado['success']) {
            $this->setFlashMessage('success', 'Archivo subido correctamente.');
            header("Location: " . BASE_URL . "soportes/ver/{$ticketId}#files");
        } else {
            $this->setFlashMessage('error', $resultado['error']);
            header("Location: " . BASE_URL . "soportes/ver/{$ticketId}#files");
        }
        exit;
    }

    /**
     * Descarga un archivo adjunto.
     */
    public function descargar_archivo($id)
    {
        $resultado = $this->fileUploadService->descargarArchivo($id);
        
        if (!$resultado['success']) {
            die($resultado['error']);
        }

        $this->fileUploadService->servirArchivo($resultado['archivo'], $resultado['ruta']);
        exit;
    }

    /**
     * Elimina un archivo adjunto.
     */
    public function eliminar_archivo($id)
    {
        $this->restrictTo(['admin', 'tecnico']);
        
        // Obtener info del archivo antes de eliminar para saber a qué ticket pertenece
        $archivo = (new \App\Models\TicketArchivo())->findById($id);
        
        if (!$archivo) {
            $_SESSION['flash_error'] = 'Archivo no encontrado.';
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        }
        
        $ticketId = $archivo->ticket_id;
        
        // Eliminar archivo
        if ($this->fileUploadService->eliminarArchivo($id)) {
            $_SESSION['flash_success'] = 'Archivo eliminado correctamente.';
        } else {
            $_SESSION['flash_error'] = 'Error al eliminar el archivo.';
        }
        
        // Redirigir al ticket
        header('Location: ' . BASE_URL . 'soportes/ver/' . $ticketId . '#files');
        exit;
    }

    /**
     * Agrega un comentario a un ticket.
     */
    public function agregar_comentario()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        }

        $ticketId = filter_input(INPUT_POST, 'soporte_id', FILTER_SANITIZE_NUMBER_INT);
        // Use FILTER_DEFAULT to store raw HTML/text (PDO handles SQL injection).
        // HTML escaping happens at the View layer (htmlspecialchars).
        $comentario = filter_input(INPUT_POST, 'contenido', FILTER_DEFAULT);
        $esInterno = isset($_POST['es_interno']) ? 1 : 0;

        if (!$ticketId || empty($comentario)) {
            $this->setFlashMessage('error', 'El comentario no puede estar vacío.');
            header("Location: " . BASE_URL . "soportes/ver/{$ticketId}");
            exit;
        }

        if ($esInterno && !in_array($_SESSION['rol'], ['admin', 'tecnico'])) {
            $esInterno = 0;
        }

        $datosComentario = [
            'ticket_id' => $ticketId,
            'usuario_id' => $_SESSION['user_id'],
            'comentario' => $comentario,
            'es_interno' => $esInterno
        ];

        $newId = $this->ticketComentarioModel->create($datosComentario);

        if ($newId) {
            // Check for AJAX request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                
                // Fetch the created comment to return full data (like date, id)
                // $newId is already the ID.
                
                // Let's return success and the data needed to build the UI
                $user = $this->usuarioModel->findById($_SESSION['user_id']); // Need user name
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Comentario agregado',
                    'comentario' => [
                        'id' => $newId, 
                        'contenido' => htmlspecialchars($comentario), // Escape for JS consumption
                        'author' => $user->username ?? 'Usuario',
                        'initials' => strtoupper(substr($user->username ?? 'U', 0, 2)),
                        'date' => 'Ahora mismo',
                        'is_internal' => (bool)$esInterno
                    ]
                ]);
                exit;
            }

            $this->setFlashMessage('success', 'Comentario agregado correctamente.');
            
            if (!$esInterno && in_array($_SESSION['rol'], ['admin', 'tecnico'])) {
                $soporte = $this->soporteModel->findById($ticketId);
                if ($soporte && $soporte->usuario_creacion_id != $_SESSION['user_id']) {
                    $this->notificacionModel->createNotification(
                        $soporte->usuario_creacion_id,
                        "Nuevo comentario en tu ticket #{$ticketId}",
                        "/soportes/ver/{$ticketId}"
                    );
                }
            }
        } else {
             if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error al guardar el comentario.']);
                exit;
            }
            $this->setFlashMessage('error', 'Error al guardar el comentario.');
        }

        header("Location: " . BASE_URL . "soportes/ver/{$ticketId}#comments");
        exit;
    }

    /**
     * Edita un comentario existente.
     */
    public function editar_comentario()
    {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        }

        $comentarioId = filter_input(INPUT_POST, 'comentario_id', FILTER_SANITIZE_NUMBER_INT);
        $ticketId = filter_input(INPUT_POST, 'soporte_id', FILTER_SANITIZE_NUMBER_INT);
        $nuevoContenido = filter_input(INPUT_POST, 'contenido', FILTER_DEFAULT);

        if (!$comentarioId || !$ticketId || empty($nuevoContenido)) {
            $this->setFlashMessage('error', 'Datos inválidos.');
            header("Location: " . BASE_URL . "soportes/ver/{$ticketId}#comments");
            exit;
        }

        $comentario = $this->ticketComentarioModel->findById($comentarioId);
        
        // Verificar permisos: dueño del comentario o admin, y que pertenezca al ticket
        if (!$comentario || $comentario->ticket_id != $ticketId) {
            $this->setFlashMessage('error', 'Comentario no encontrado.');
            header("Location: " . BASE_URL . "soportes/ver/{$ticketId}#comments");
            exit;
        }

        if ($comentario->usuario_id != $_SESSION['user_id'] && $_SESSION['rol'] !== 'admin') {
            $this->setFlashMessage('error', 'No tienes permiso para editar este comentario.');
            header("Location: " . BASE_URL . "soportes/ver/{$ticketId}#comments");
            exit;
        }

        if ($this->ticketComentarioModel->update($comentarioId, ['comentario' => $nuevoContenido])) {
            $this->setFlashMessage('success', 'Comentario actualizado.');
        } else {
            $this->setFlashMessage('error', 'Error al actualizar el comentario.');
        }

        header("Location: " . BASE_URL . "soportes/ver/{$ticketId}#comments");
        exit;
    }

    /**
     * Elimina un comentario.
     */
    public function eliminar_comentario($id)
    {
        // En este caso el ID viene por URL, pero necesitamos el ticketId para redirigir
        // Lo buscaremos primero
        $comentario = $this->ticketComentarioModel->findById($id);

        if (!$comentario) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Comentario no encontrado.'], 404);
            }
            $this->setFlashMessage('error', 'Comentario no encontrado.');
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        }

        $ticketId = $comentario->ticket_id;

        // Verificar permisos: dueño o admin
        if ($comentario->usuario_id != $_SESSION['user_id'] && $_SESSION['rol'] !== 'admin') {
            if ($this->isAjax()) {
                 $this->jsonResponse(['success' => false, 'message' => 'No tienes permiso para eliminar este comentario.'], 403);
            }
            $this->setFlashMessage('error', 'No tienes permiso para eliminar este comentario.');
            header("Location: " . BASE_URL . "soportes/ver/{$ticketId}#comments");
            exit;
        }

        if ($this->ticketComentarioModel->delete($id)) {
            if ($this->isAjax()) {
                 $this->jsonResponse(['success' => true, 'message' => 'Comentario eliminado.']);
            }
            $this->setFlashMessage('success', 'Comentario eliminado.');
        } else {
             if ($this->isAjax()) {
                 $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar el comentario.'], 500);
            }
            $this->setFlashMessage('error', 'Error al eliminar el comentario.');
        }

        header("Location: " . BASE_URL . "soportes/ver/{$ticketId}#comments");
        exit;
    }

    public function eliminar_comentarios_masivos()
    {
        if (!$this->isAjax()) {
             http_response_code(405);
             exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $ids = $input['ids'] ?? [];

        if (empty($ids)) {
            $this->jsonResponse(['success' => false, 'message' => 'No se seleccionaron comentarios.'], 400);
        }

        $deletedCount = 0;
        $errors = 0;

        foreach ($ids as $id) {
            $comentario = $this->ticketComentarioModel->findById($id);
            if ($comentario) {
                // Check permissions for each
                if ($comentario->usuario_id == $_SESSION['user_id'] || $_SESSION['rol'] === 'admin') {
                    if ($this->ticketComentarioModel->delete($id)) {
                        $deletedCount++;
                    } else {
                        $errors++;
                    }
                }
            }
        }

        if ($deletedCount > 0) {
            $this->jsonResponse(['success' => true, 'message' => "Se eliminaron $deletedCount comentarios.", 'deletedCount' => $deletedCount]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'No se pudo eliminar ningún comentario (permisos o error).'], 500);
        }
    }

    public function eliminar_tickets_masivos()
    {
        // Solo admin puede borrar masivamente tickets (por seguridad)
        $this->restrictTo(['admin']);

        if (!$this->isAjax()) {
             http_response_code(405);
             exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $ids = $input['ids'] ?? [];

        if (empty($ids)) {
            $this->jsonResponse(['success' => false, 'message' => 'No se seleccionaron tickets.'], 400);
        }

        $deletedCount = 0;
        $errors = 0;

        foreach ($ids as $id) {
            // Permission check handled inside eliminarTicket (service) or restrictTo above.
            // ticketService->eliminarTicket handles logic.
            if ($this->ticketService->eliminarTicket($id)) {
                $deletedCount++;
                 $this->logBitacora("Eliminó ticket #{$id} (Masivo)", 'soporte', $id);
            } else {
                $errors++;
            }
        }

        if ($deletedCount > 0) {
            $this->jsonResponse(['success' => true, 'message' => "Se eliminaron $deletedCount tickets.", 'deletedCount' => $deletedCount]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'No se pudo eliminar ningún ticket.'], 500);
        }
    }

    /**
     * Elimina un ticket de soporte.
     */
    public function eliminar(int $id)
    {
        $this->restrictTo(['admin', 'consultor', 'tecnico']);

        if (!$this->ticketService->puedeEditar($id, $_SESSION['user_id'], $_SESSION['rol'])) {
            $this->setFlashMessage('error', 'No tienes permiso para eliminar este ticket.');
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        }

        if ($this->ticketService->eliminarTicket($id)) {
            $this->setFlashMessage('success', "Ticket #{$id} eliminado correctamente.");
            $this->logBitacora("Eliminó ticket #{$id}", 'soporte', $id);
        } else {
            $this->setFlashMessage('error', "Error al eliminar el ticket.");
        }

        header("Location: " . BASE_URL . "soportes");
        exit;
    }

    /**
     * Guardar firma del usuario
     */
    public function guardar_firma()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        }

        $ticketId = $_POST['ticket_id'] ?? 0;
        $firmaBase64 = $_POST['firma_base64'] ?? '';

        if (empty($ticketId) || empty($firmaBase64)) {
            $this->setFlashMessage('error', 'Datos de firma inválidos.');
            header("Location: " . BASE_URL . "soportes/ver/{$ticketId}");
            exit;
        }

        if ($this->ticketService->guardarFirma($ticketId, $firmaBase64)) {
            $this->setFlashMessage('success', 'Firma guardada correctamente.');
        } else {
            $this->setFlashMessage('error', 'Error al guardar la firma.');
        }

        header("Location: " . BASE_URL . "soportes/ver/{$ticketId}#signature");
        exit;
    }

    /**
     * Guarda la valoración del servicio
     */
    public function guardar_valoracion()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        }

        $soporteId = filter_input(INPUT_POST, 'soporte_id', FILTER_SANITIZE_NUMBER_INT);
        $valoracion = filter_input(INPUT_POST, 'valoracion', FILTER_SANITIZE_SPECIAL_CHARS);
        $comentario = filter_input(INPUT_POST, 'comentario_valoracion', FILTER_SANITIZE_SPECIAL_CHARS);

        $valoracionesValidas = ['excelente', 'bueno', 'regular', 'malo'];
        
        if (!$soporteId || !in_array($valoracion, $valoracionesValidas)) {
            $this->setFlashMessage('error', 'Valoración inválida.');
            header("Location: " . BASE_URL . "soportes/ver/{$soporteId}");
            exit;
        }

        $soporte = $this->soporteModel->findById($soporteId);
        if (!$soporte || $soporte->estado !== 'resuelto') {
            $this->setFlashMessage('error', 'Solo se pueden valorar tickets resueltos.');
            header("Location: " . BASE_URL . "soportes/ver/{$soporteId}");
            exit;
        }

        $datos = [
            'valoracion' => $valoracion,
            'comentario_valoracion' => $comentario ?: null,
            'fecha_valoracion' => date('Y-m-d H:i:s')
        ];

        if ($this->soporteModel->update($soporteId, $datos)) {
            $this->setFlashMessage('success', '¡Gracias por tu valoración!');
            $this->logBitacora("Valoró el ticket #{$soporteId} como '{$valoracion}'", 'soporte', $soporteId);
        } else {
            $this->setFlashMessage('error', 'Error al guardar la valoración.');
        }

        header("Location: " . BASE_URL . "soportes/ver/{$soporteId}#signature");
        exit;
    }

    /**
     * Generar PDF del ticket
     */
    public function pdf(int $id)
    {
        $soporte = $this->soporteModel->findByIdWithDetails($id);

        if (!$soporte) {
            die("Ticket no encontrado.");
        }

        $archivos = $this->ticketArchivoModel->findByTicketId($id);
        $comentarios = $this->ticketComentarioModel->findByTicketId($id, false);

        ob_start();
        extract([
            'soporte' => $soporte,
            'archivos' => $archivos,
            'comentarios' => $comentarios
        ]);
        require_once '../src/Views/soportes/pdf.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("ticket_{$id}.pdf", ["Attachment" => false]);
    }
    /**
     * Guarda las observaciones técnicas (Bitácora).
     */
    public function guardar_observaciones()
    {
        $this->restrictTo(['admin', 'tecnico']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        }

        $soporteId = filter_input(INPUT_POST, 'soporte_id', FILTER_SANITIZE_NUMBER_INT);
        $observaciones = filter_input(INPUT_POST, 'observaciones', FILTER_DEFAULT);

        if (!$soporteId) {
            $this->respondError('ID de soporte inválido', $soporteId);
            exit;
        }

        $soporte = $this->soporteModel->findById($soporteId);
        if (!$soporte) {
            $this->respondError('Ticket no encontrado', $soporteId);
            exit;
        }

        // Update observations
        if ($this->soporteModel->update($soporteId, ['observaciones' => $observaciones])) {
            $this->logBitacora("Actualizó bitácora técnica", 'soporte', $soporteId);
            
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Bitácora actualizada correctamente']);
                exit;
            }
            
            $this->setFlashMessage('success', 'Bitácora actualizada correctamente.');
        } else {
            $this->respondError('Error al guardar la bitácora', $soporteId);
            exit;
        }

        header("Location: " . BASE_URL . "soportes/ver/{$soporteId}#notes");
        exit;
    }

    private function respondError($message, $ticketId) {
        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
            $this->setFlashMessage('error', $message);
            if ($ticketId) {
                header("Location: " . BASE_URL . "soportes/ver/{$ticketId}#notes");
            } else {
                header('Location: ' . BASE_URL . 'soportes');
            }
        }
    }

    private function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * API para buscar técnicos (JSON)
     * Utilizado por el modal de "Asignar Técnico"
     */
    public function buscarTecnicos() {
        // Asegurar respuesta JSON
        header('Content-Type: application/json');

        try {
            // Verificar sesión (user_id es la variable correcta en este sistema)
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }

            // Obtener técnicos de la base de datos
            $tecnicos = $this->usuarioModel->findAllTechnicians();
            
            $results = [];
            foreach ($tecnicos as $tecnico) {
                // Contar tickets activos (en_proceso o pendiente) asignados a este empleado
                $activeTickets = $this->soporteModel->countActiveByEmpleado($tecnico->empleado_id ?? 0);
                
                // Parsear nombre completo si viene concatenado
                $nombreParts = explode(' ', $tecnico->nombre_completo ?? '', 2);
                $nombre = $nombreParts[0] ?? 'Técnico';
                $apellido = $nombreParts[1] ?? '';
                
                $results[] = [
                    'id' => $tecnico->empleado_id,
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'status' => $activeTickets > 3 ? 'busy' : 'available',
                    'active_tickets' => $activeTickets,
                    'specialty' => 'Soporte Técnico' // Puedes agregar campo de especialidad si existe
                ];
            }
            
            echo json_encode($results);
            exit;

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al buscar técnicos: ' . $e->getMessage()]);
            exit;
        }
    }
}

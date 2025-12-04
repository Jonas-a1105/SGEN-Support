<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Soporte;
use App\Models\Equipo;
use App\Models\Departamento;
use App\Models\Usuario;
use App\Models\Inventario;
use App\Models\Categoria;
use App\Models\TicketArchivo;
use App\Models\TicketComentario;
use App\Models\Notificacion;
use Dompdf\Dompdf;
use Dompdf\Options;

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

    public function __construct()
    {
        parent::__construct();

        $this->soporteModel      = new Soporte();
        $this->equipoModel       = new Equipo();
        $this->departamentoModel = new Departamento();
        $this->usuarioModel      = new Usuario();
        $this->inventarioModel   = new Inventario();
        $this->categoriaModel    = new Categoria();
        $this->ticketArchivoModel = new TicketArchivo();
        $this->ticketComentarioModel = new TicketComentario();
        $this->notificacionModel = new Notificacion();
    }

    /**
     * Lista de soportes (visible para todos los roles).
     */
    public function index()
    {
        if ($_SESSION['rol'] === 'consultor') {
            $departamentoId = $_SESSION['departamento_id'] ?? 0;
            $soportes = $this->soporteModel->findAllByDepartment($departamentoId);
        } else {
            // Admin y Técnico ven todos los tickets
            $soportes = $this->soporteModel->findAllWithDetails();
        }
        
        // Calcular KP Is para el dashboard
        $kpis = [
            'pendientes' => 0,
            'en_proceso' => 0,
            'resueltos' => 0,
            'alta_prioridad' => 0
        ];
        
        foreach ($soportes as $soporte) {
            if ($soporte->estado === 'pendiente') $kpis['pendientes']++;
            if ($soporte->estado === 'en_proceso') $kpis['en_proceso']++;
            if ($soporte->estado === 'resuelto') $kpis['resueltos']++;
            if ($soporte->prioridad === 'alta' && $soporte->estado !== 'resuelto') {
                $kpis['alta_prioridad']++;
            }
        }
        
        // Obtener categorías para filtro
        $categorias = $this->categoriaModel->findAll();

        $this->render('soportes/lista', [
            'soportes' => $soportes,
            'kpis' => $kpis,
            'categorias' => $categorias,
            'titulo'   => 'Listado de Soportes'
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

        // Obtener consumos de inventario
        $consumos = $this->soporteModel->getConsumos($id);
        
        // Obtener items del inventario del departamento del equipo
        $equipo = $this->equipoModel->findById($soporte->equipo_id);
        $departamentoId = $equipo->departamento_id ?? null;
        
        $items = [];
        if ($departamentoId) {
            $items = $this->inventarioModel->obtenerItemsPorDepartamento($departamentoId);
        }

        // Obtener comentarios
        $includeInternos = in_array($_SESSION['rol'], ['admin', 'tecnico']);
        $comentarios = $this->ticketComentarioModel->findByTicketId($id, $includeInternos);

        $this->render('soportes/detalle', [
            'titulo'  => "Detalle de Soporte #{$soporte->id}",
            'soporte' => $soporte,
            'consumos' => $consumos,
            'items' => $items,
            'comentarios' => $comentarios
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
            // Técnicos y Consultores solo ven su propio departamento
            $departamentos = $this->departamentoModel->findById($departamentoId);
            $departamentos = $departamentos ? [$departamentos] : [];
        } else {
            $equipos       = $this->equipoModel->findAll();
            $departamentos = $this->departamentoModel->findAll();
        }

        $categorias = $this->categoriaModel->findAllActive();

        $this->render('soportes/formulario', [
            'titulo'           => 'Crear Nuevo Ticket de Soporte',
            'equipo_list'      => $equipos,
            'departamento_list'=> $departamentos,
            'categoria_list'   => $categorias,
            'soporte'          => null
        ]);
    }

    /**
     * Formulario de edición de soporte.
     */
    public function editar(int $id)
    {
        $this->restrictTo(['admin', 'tecnico', 'consultor']);

        $soporte = $this->soporteModel->findById($id);
        if (!$soporte) {
            die("Ticket de soporte no encontrado.");
        }

        // Restricción para técnicos y consultores: solo pueden editar sus propios tickets
        if (($_SESSION['rol'] === 'tecnico' || $_SESSION['rol'] === 'consultor') && $soporte->usuario_creacion_id != $_SESSION['user_id']) {
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
            $equipos       = $this->equipoModel->findAll();
            $departamentos = $this->departamentoModel->findAll();
        }

        $categorias = $this->categoriaModel->findAllActive();

        $this->render('soportes/formulario', [
            'titulo'           => "Editar Ticket #{$id}",
            'equipo_list'      => $equipos,
            'departamento_list'=> $departamentos,
            'categoria_list'   => $categorias,
            'soporte'          => $soporte
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

        $id          = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $equipo_id   = filter_input(INPUT_POST, 'equipo_id', FILTER_SANITIZE_NUMBER_INT);
        $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS);
        $prioridad   = filter_input(INPUT_POST, 'prioridad', FILTER_SANITIZE_SPECIAL_CHARS);
        $categoria_id = filter_input(INPUT_POST, 'categoria_id', FILTER_SANITIZE_NUMBER_INT);

        if (empty($descripcion) || empty($equipo_id)) {
            $this->setFlashMessage('error', 'Error: Faltan datos obligatorios (Descripción y Equipo).');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        // VALIDACIÓN: Técnico/Consultor solo puede crear tickets para equipos de su departamento
        if ($_SESSION['rol'] === 'tecnico' || $_SESSION['rol'] === 'consultor') {
            $equipo = $this->equipoModel->findById($equipo_id);
            if (!$equipo) {
                $this->setFlashMessage('error', 'Error: Equipo no encontrado.');
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
            $userDeptId = $_SESSION['departamento_id'] ?? null;
            if ($equipo->departamento_id != $userDeptId) {
                $this->setFlashMessage('error', 'No tiene permisos para crear tickets para equipos fuera de su departamento.');
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
        }

        $datos = [
            'equipo_id'   => $equipo_id,
            'descripcion' => $descripcion,
            'prioridad'   => $prioridad ?? 'media',
            'categoria_id' => $categoria_id ?: null,
        ];

        if ($id) {
            $this->restrictTo(['admin', 'consultor', 'tecnico']);

            // Validar propiedad del ticket para consultores y técnicos
            if ($_SESSION['rol'] === 'consultor' || $_SESSION['rol'] === 'tecnico') {
                $soporte = $this->soporteModel->findById($id);
                if (!$soporte || $soporte->usuario_creacion_id != $_SESSION['user_id']) {
                    $this->setFlashMessage('error', 'No tienes permiso para editar este ticket.');
                    header('Location: ' . BASE_URL . 'soportes');
                    exit;
                }
            }

            if ($this->soporteModel->update($id, $datos)) {
                $this->setFlashMessage('success', "Ticket #{$id} actualizado correctamente.");
                $this->logBitacora("Actualizó ticket #{$id}", 'soporte', $id); // Log
            }
            $redirect_id = $id;
        } else {
            $datos['fecha']               = date('Y-m-d H:i:s');
            $datos['estado']              = 'pendiente';
            $datos['usuario_creacion_id'] = $_SESSION['user_id'] ?? null;

            if ($redirect_id = $this->soporteModel->create($datos)) {
                $this->setFlashMessage('success', 'Nuevo ticket de soporte creado exitosamente.');
                $this->logBitacora("Creó ticket #{$redirect_id}", 'soporte', $redirect_id); // Log
                
                // Actualizar estado del equipo a "En reparación"
                $this->equipoModel->update($equipo_id, ['estado' => 'en_reparacion']);
                
                // Notificar a todos los técnicos
                $tecnicos = $this->usuarioModel->findAllTechnicians();
                foreach ($tecnicos as $tech) {
                    // Asegurarse de tener usuario_id (depende de la corrección en UsuarioModel)
                    $techId = $tech->usuario_id ?? $tech->id; // Fallback si no se corrigió
                    if ($techId) {
                        $this->notificacionModel->createNotification(
                            $techId,
                            "Nuevo ticket #{$redirect_id} creado.",
                            "/soportes/ver/{$redirect_id}"
                        );
                    }
                }
                
                // Notificar a admins si no es admin quien crea
                $this->notifyAdmins(
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
            'titulo'  => "Asignar Técnico a Soporte #{$id}",
            'soporte' => $soporte,
            'tecnicos'=> $tecnicos
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

        $soporte_id  = filter_input(INPUT_POST, 'soporte_id', FILTER_SANITIZE_NUMBER_INT);
        $empleado_id = filter_input(INPUT_POST, 'empleado_id', FILTER_SANITIZE_NUMBER_INT);

        if (empty($soporte_id) || empty($empleado_id)) {
            die("Error: Faltan IDs para la asignación.");
        }

        $updateData = [
            'empleado_id' => $empleado_id,
            'estado'      => 'en_proceso'
        ];

        if ($this->soporteModel->update($soporte_id, $updateData)) {
            $this->logBitacora("Asignó ticket #{$soporte_id} a empleado #{$empleado_id}", 'soporte', $soporte_id); // Log
            
            // Buscar el usuario_id asociado al empleado para notificarle
            // Esto requiere consultar el empleado para obtener su usuario_id
            // Por simplicidad, asumimos que el modelo Empleado tiene findById
            $empleado = (new \App\Models\Empleado())->findById($empleado_id);
            if ($empleado && $empleado->usuario_id) {
                $this->notificacionModel->createNotification(
                    $empleado->usuario_id,
                    "Te han asignado el ticket #{$soporte_id}",
                    "/soportes/ver/{$soporte_id}"
                );
            }

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

        $soporte = $this->soporteModel->findById($id);
        if (!$soporte) {
            $this->setFlashMessage('error', "Error: Ticket #{$id} no encontrado.");
            header("Location: " . BASE_URL . "soportes");
            exit;
        }

        if ($soporte->estado !== 'en_proceso') {
            $this->setFlashMessage('error', "Error: El ticket debe estar 'en proceso' para resolverse (Estado actual: {$soporte->estado}).");
            header("Location: " . BASE_URL . "soportes/ver/{$id}");
            exit;
        }

        $fechaCierre = date('Y-m-d H:i:s');
        $tiempoMinutos = (strtotime($fechaCierre) - strtotime($soporte->fecha)) / 60;

        $updateData = [
            'estado'       => 'resuelto',
            'fecha_cierre' => $fechaCierre,
            'tiempo_atencion_minutos' => round($tiempoMinutos)
        ];

        if ($this->soporteModel->update($id, $updateData)) {
            $this->setFlashMessage('success', "¡Ticket #{$id} marcado como RESUELTO!");
            $this->logBitacora("Resolvió ticket #{$id}", 'soporte', $id); // Log
            
            // Notificar al creador si existe
            if ($soporte->usuario_creacion_id) {
                $this->notificacionModel->createNotification(
                    $soporte->usuario_creacion_id,
                    "Tu ticket #{$id} ha sido resuelto.",
                    "/soportes/ver/{$id}"
                );
            }
            
            // Notificar a admins si un técnico resuelve
            $this->notifyAdmins(
                "{$_SESSION['username']} resolvió el ticket #{$id}",
                "/soportes/ver/{$id}"
            );
        } else {
            $this->setFlashMessage('error', "Error fatal al actualizar el ticket en la base de datos.");
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

        $soporte = $this->soporteModel->findById($id);
        if (!$soporte) {
            $this->setFlashMessage('error', "Error: Ticket #{$id} no encontrado.");
            header("Location: " . BASE_URL . "soportes");
            exit;
        }

        if ($soporte->estado !== 'en_proceso') {
            $this->setFlashMessage('error', "Error: El ticket debe estar 'en proceso' para ponerse en espera.");
            header("Location: " . BASE_URL . "soportes/ver/{$id}");
            exit;
        }

        $updateData = [
            'estado' => 'en_espera'
        ];

        if ($this->soporteModel->update($id, $updateData)) {
            $this->setFlashMessage('success', "Ticket #{$id} puesto EN ESPERA.");
            $this->logBitacora("Puso en espera ticket #{$id}", 'soporte', $id); // Log
        } else {
            $this->setFlashMessage('error', "Error al actualizar el estado del ticket.");
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

        $soporte = $this->soporteModel->findById($id);
        if (!$soporte) {
            $this->setFlashMessage('error', "Error: Ticket #{$id} no encontrado.");
            header("Location: " . BASE_URL . "soportes");
            exit;
        }

        if ($soporte->estado !== 'en_espera') {
            $this->setFlashMessage('error', "Error: El ticket debe estar 'en espera' para reanudarse.");
            header("Location: " . BASE_URL . "soportes/ver/{$id}");
            exit;
        }

        $updateData = [
            'estado' => 'en_proceso'
        ];

        if ($this->soporteModel->update($id, $updateData)) {
            $this->setFlashMessage('success', "Ticket #{$id} REANUDADO (En Proceso).");
            $this->logBitacora("Reanudó ticket #{$id}", 'soporte', $id); // Log
        } else {
            $this->setFlashMessage('error', "Error al actualizar el estado del ticket.");
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

        // Obtener el departamento del equipo del soporte
        $soporte = $this->soporteModel->findByIdWithDetails($soporteId);
        $equipo = $this->equipoModel->findById($soporte->equipo_id);
        $departamentoId = $equipo->departamento_id ?? null;

        if (!$departamentoId) {
            $this->setFlashMessage('error', 'El equipo no tiene un departamento asignado.');
            header('Location: ' . BASE_URL . "soportes/ver/{$soporteId}");
            exit;
        }

        try {
            // Guardar el consumo (se descuenta el stock automáticamente)
            $this->inventarioModel->consumirEnTicket($itemId, $soporteId, $cantidad, $usuarioId, $departamentoId);
            $this->setFlashMessage('success', 'Consumo registrado exitosamente.');
            $this->logBitacora("Agregó consumo de inventario al ticket #{$soporteId}", 'soporte', $soporteId);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Error: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . "soportes/ver/{$soporteId}");
        exit;
    }


    /**
     * Guarda las observaciones técnicas de un ticket.
     */
    public function guardar_observaciones()
    {
        $this->restrictTo(['admin', 'tecnico']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        }

        $soporteId = filter_input(INPUT_POST, 'soporte_id', FILTER_SANITIZE_NUMBER_INT);
        $observaciones = $_POST['observaciones'] ?? '';

        if (!$soporteId) {
            $this->setFlashMessage('error', 'ID de ticket inválido.');
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        }

        $soporte = $this->soporteModel->findById($soporteId);
        if (!$soporte) {
            $this->setFlashMessage('error', 'Ticket no encontrado.');
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        }

        // Solo admin puede editar observaciones de tickets resueltos
        if ($soporte->estado == 'resuelto' && $_SESSION['rol'] != 'admin') {
            $this->setFlashMessage('error', 'No se pueden modificar observaciones de tickets cerrados.');
            header("Location: " . BASE_URL . "soportes/ver/{$soporteId}");
            exit;
        }

        // Actualizar observaciones
        if ($this->soporteModel->update($soporteId, ['observaciones' => $observaciones])) {
            $this->setFlashMessage('success', 'Observaciones guardadas correctamente.');
            $this->logBitacora("Guardó observaciones en Ticket #{$soporteId}", 'soporte', $soporteId);
        } else {
            $this->setFlashMessage('error', 'Error al guardar observaciones.');
        }

        header("Location: " . BASE_URL . "soportes/ver/{$soporteId}");
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

        $soporte = $this->soporteModel->findById($soporteId);
        if (!$soporte) {
            $this->setFlashMessage('error', 'Ticket no encontrado.');
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        }

        // Convertir el formato datetime-local a MySQL datetime
        $fechaCierreMySQL = date('Y-m-d H:i:s', strtotime($nuevaFechaCierre));

        // Recalcular tiempo de atención
        $tiempoMinutos = (strtotime($fechaCierreMySQL) - strtotime($soporte->fecha)) / 60;

        // Actualizar fecha de cierre y tiempo de atención
        if ($this->soporteModel->update($soporteId, [
            'fecha_cierre' => $fechaCierreMySQL,
            'tiempo_atencion_minutos' => round($tiempoMinutos)
        ])) {
            $this->setFlashMessage('success', 'Fecha de cierre actualizada y tiempo de atención recalculado.');
            $this->logBitacora("Actualizó fecha de cierre del Ticket #{$soporteId} a {$fechaCierreMySQL}", 'soporte', $soporteId);
        } else {
            $this->setFlashMessage('error', 'Error al actualizar la fecha de cierre.');
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

        $ticketId = filter_input(INPUT_POST, 'ticket_id', FILTER_SANITIZE_NUMBER_INT);
        
        if (!$ticketId || empty($_FILES['archivo'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos']);
            exit;
        }

        $archivo = $_FILES['archivo'];
        $nombreOriginal = $archivo['name'];
        $tipoMime = $archivo['type'];
        $tamanoBytes = $archivo['size'];
        $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        // Validaciones
        $extPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
        if (!in_array($ext, $extPermitidas)) {
            http_response_code(400);
            echo json_encode(['error' => 'Tipo de archivo no permitido']);
            exit;
        }

        if ($tamanoBytes > 5 * 1024 * 1024) { // 5MB
            http_response_code(400);
            echo json_encode(['error' => 'El archivo excede el tamaño máximo de 5MB']);
            exit;
        }

        // Generar nombre único
        $nombreUnico = 'ticket_' . $ticketId . '_' . time() . '_' . uniqid() . '.' . $ext;
        $rutaDestino = 'public/uploads/tickets/' . $nombreUnico;
        $rutaAbsoluta = __DIR__ . '/../../' . $rutaDestino;

        if (move_uploaded_file($archivo['tmp_name'], $rutaAbsoluta)) {
            $datosArchivo = [
                'ticket_id' => $ticketId,
                'nombre_archivo' => $nombreUnico,
                'nombre_original' => $nombreOriginal,
                'ruta' => $rutaDestino,
                'tipo_mime' => $tipoMime,
                'tamaño_bytes' => $tamanoBytes,
                'subido_por' => $_SESSION['user_id'] ?? null
            ];

            if ($this->ticketArchivoModel->create($datosArchivo)) {
                echo json_encode(['success' => true, 'mensaje' => 'Archivo subido correctamente']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al guardar en base de datos']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al mover el archivo al servidor']);
        }
        exit;
    }

    /**
     * Descarga un archivo adjunto.
     */
    public function descargar_archivo($id)
    {
        $archivo = $this->ticketArchivoModel->findById($id);
        
        if (!$archivo) {
            die('Archivo no encontrado');
        }

        $rutaAbsoluta = __DIR__ . '/../../' . $archivo->ruta;

        if (file_exists($rutaAbsoluta)) {
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $archivo->tipo_mime);
            header('Content-Disposition: attachment; filename="' . $archivo->nombre_original . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($rutaAbsoluta));
            readfile($rutaAbsoluta);
            exit;
        } else {
            die('El archivo físico no existe');
        }
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

        $ticketId = filter_input(INPUT_POST, 'ticket_id', FILTER_SANITIZE_NUMBER_INT);
        $comentario = filter_input(INPUT_POST, 'comentario', FILTER_SANITIZE_SPECIAL_CHARS);
        $esInterno = isset($_POST['es_interno']) ? 1 : 0;

        if (!$ticketId || empty($comentario)) {
            $this->setFlashMessage('error', 'El comentario no puede estar vacío.');
            header("Location: " . BASE_URL . "soportes/ver/{$ticketId}");
            exit;
        }

        // Validar permisos para comentarios internos
        if ($esInterno && !in_array($_SESSION['rol'], ['admin', 'tecnico'])) {
            $esInterno = 0; // Forzar a público si no tiene permisos
        }

        $datosComentario = [
            'ticket_id' => $ticketId,
            'usuario_id' => $_SESSION['user_id'],
            'comentario' => $comentario,
            'es_interno' => $esInterno
        ];

        if ($this->ticketComentarioModel->create($datosComentario)) {
            $this->setFlashMessage('success', 'Comentario agregado correctamente.');
            
            // Notificar si es un comentario público de un técnico/admin al usuario creador
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
            $this->setFlashMessage('error', 'Error al guardar el comentario.');
        }

        header("Location: " . BASE_URL . "soportes/ver/{$ticketId}");
        exit;
    }

    /**
     * Elimina un ticket de soporte.
     */
    public function eliminar(int $id)
    {
        $this->restrictTo(['admin', 'consultor', 'tecnico']);

        $soporte = $this->soporteModel->findById($id);
        if (!$soporte) {
            $this->setFlashMessage('error', "Error: Ticket #{$id} no encontrado.");
            header("Location: " . BASE_URL . "soportes");
            exit;
        }

        // Restricción para consultores y técnicos: solo pueden eliminar sus propios tickets
        if (($_SESSION['rol'] === 'consultor' || $_SESSION['rol'] === 'tecnico') && $soporte->usuario_creacion_id != $_SESSION['user_id']) {
            $this->setFlashMessage('error', 'No tienes permiso para eliminar este ticket.');
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        }

        if ($this->soporteModel->delete($id)) {
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

        // Actualizar solo el campo de firma
        if ($this->soporteModel->update($ticketId, ['firma_usuario' => $firmaBase64])) {
            $this->setFlashMessage('success', 'Firma guardada correctamente.');
        } else {
            $this->setFlashMessage('error', 'Error al guardar la firma.');
        }

        header("Location: " . BASE_URL . "soportes/ver/{$ticketId}");
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

        // Obtener datos adicionales para el PDF
        $archivos = $this->ticketArchivoModel->findByTicketId($id);
        $comentarios = $this->ticketComentarioModel->findByTicketId($id, false); // Solo comentarios públicos

        // Renderizar vista a HTML (capturar salida)
        ob_start();
        extract([
            'soporte' => $soporte,
            'archivos' => $archivos,
            'comentarios' => $comentarios
        ]);
        require_once '../src/Views/soportes/pdf.php';
        $html = ob_get_clean();

        // Configurar Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Stream PDF
        $dompdf->stream("ticket_{$id}.pdf", ["Attachment" => false]);
    }
}

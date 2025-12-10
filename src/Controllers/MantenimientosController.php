<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Mantenimiento;
use App\Models\Equipo;
use App\Models\Usuario; // Importar Usuario
use App\Models\Notificacion;

class MantenimientosController extends Controller
{
    private $mantenimientoModel;
    private $equipoModel;
    private $usuarioModel; // Propiedad
    private $notificacionModel;

    public function __construct()
    {
        parent::__construct();
        $this->restrictTo(['admin', 'tecnico']);
        $this->mantenimientoModel = new Mantenimiento();
        $this->equipoModel = new Equipo();
        $this->usuarioModel = new Usuario(); // Inicializar
        $this->notificacionModel = new Notificacion();
    }

    public function index()
    {
        if ($_SESSION['rol'] === 'tecnico') {
            $departamentoId = $_SESSION['departamento_id'] ?? 0;
            $mantenimientos = $this->mantenimientoModel->findAllByDepartment($departamentoId);
        } else {
            $mantenimientos = $this->mantenimientoModel->findAllWithDetails();
        }
        
        // Actualizar estados automáticamente antes de mostrar
        $this->updateAutomaticStatuses();

        $this->render('mantenimientos/lista', [
            'titulo' => 'Gestión de Mantenimientos',
            'mantenimientos' => $mantenimientos,
        ]);
    }

    public function ver(int $id)
    {
        $mantenimiento = $this->mantenimientoModel->findByIdWithDetails($id);
        if (!$mantenimiento) {
            $this->setFlashMessage('error', 'Mantenimiento no encontrado.');
            header('Location: ' . BASE_URL . 'mantenimientos');
            exit;
        }
        $this->updateAutomaticStatuses($id); // Check specific maintenance
        $mantenimiento = $this->mantenimientoModel->findByIdWithDetails($id); // Re-fetch updated

        $this->render('mantenimientos/ver', [
            'titulo' => 'Detalle de Mantenimiento',
            'mantenimiento' => $mantenimiento,
        ]);
    }

    public function dashboard()
    {
        $proximos = $this->mantenimientoModel->getProximosVencer(30);
        $todos = $this->mantenimientoModel->findAllWithDetails();
        $this->render('mantenimientos/dashboard', [
            'titulo' => 'Dashboard de Mantenimientos',
            'proximos' => $proximos,
            'todos' => $todos,
        ]);
    }

    public function crear()
    {
        $equipo_id = $_GET['equipo_id'] ?? null;
        $equipo = null;
        if ($equipo_id) {
            $equipo = $this->equipoModel->findById($equipo_id);
            // Verificar si el técnico tiene acceso a este equipo
            if ($_SESSION['rol'] === 'tecnico' && $equipo && $equipo->departamento_id != $_SESSION['departamento_id']) {
                $this->setFlashMessage('error', 'No tienes permiso para programar mantenimiento a este equipo.');
                header('Location: ' . BASE_URL . 'mantenimientos');
                exit;
            }
        }
        
        if ($_SESSION['rol'] === 'tecnico') {
            $departamentoId = $_SESSION['departamento_id'] ?? 0;
            $equipos = $this->equipoModel->findByDepartamentoId($departamentoId);
        } else {
            $equipos = $this->equipoModel->findAll();
        }
        
        $tecnicos = $this->usuarioModel->findAllTechnicians();

        $this->render('mantenimientos/formulario', [
            'titulo' => 'Registrar Mantenimiento',
            'mantenimiento' => null,
            'equipo_preseleccionado' => $equipo,
            'equipos' => $equipos,
            'tecnicos' => $tecnicos,
        ]);
    }

    public function editar(int $id)
    {
        $mantenimiento = $this->mantenimientoModel->findById($id);
        if (!$mantenimiento) {
            $this->setFlashMessage('error', 'Mantenimiento no encontrado.');
            header('Location: ' . BASE_URL . 'mantenimientos');
            exit;
        }
        
        // Restricción para técnicos: solo pueden editar mantenimientos que ellos programaron
        if ($_SESSION['rol'] === 'tecnico' && $mantenimiento->tecnico_id != $_SESSION['user_id']) {
            $this->setFlashMessage('error', 'No tienes permiso para editar este mantenimiento.');
            header('Location: ' . BASE_URL . 'mantenimientos');
            exit;
        }

        if ($_SESSION['rol'] === 'tecnico') {
            $departamentoId = $_SESSION['departamento_id'] ?? 0;
            $equipos = $this->equipoModel->findByDepartamentoId($departamentoId);
        } else {
            $equipos = $this->equipoModel->findAll();
        }

        $tecnicos = $this->usuarioModel->findAllTechnicians();

        $this->render('mantenimientos/formulario', [
            'titulo' => 'Editar Mantenimiento',
            'mantenimiento' => $mantenimiento,
            'equipos' => $equipos,
            'tecnicos' => $tecnicos,
        ]);
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'mantenimientos');
            exit;
        }
        $validator = new Validator($_POST);
        $id = $validator->getInt('id');
        $es_edicion = !empty($id);

        $validator->check('equipo_id', 'required');
        $validator->check('tipo_mantenimiento', 'required');
        $validator->check('estado', 'required');
        $validator->check('descripcion', 'required');
        $validator->check('fecha', 'required');

        if ($validator->fails()) {
            $this->setFlashMessage('error', 'Complete los campos obligatorios.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        $datos = [
            'equipo_id' => $validator->getInt('equipo_id'),
            'tipo_mantenimiento' => $validator->get('tipo_mantenimiento'),
            'estado' => $validator->get('estado'),
            'descripcion' => $validator->get('descripcion'),
            'fecha' => $validator->get('fecha'),
            'costo' => $validator->get('costo') ?: 0,
            'realizado_por' => $validator->get('realizado_por'),
            'proxima_fecha' => $validator->get('proxima_fecha') ?: null,
            'frecuencia' => $validator->get('frecuencia') ?: 'unica',
            'observaciones' => $validator->get('observaciones'),
            'duracion' => $validator->getInt('duracion'), // Nuevo campo
            'tecnico_id' => $_SESSION['user_id'] ?? null,
        ];

        // Validar que si el estado es pendiente, la fecha sea futura (opcional)
        // O si la fecha es pasada, pasarlo a 'en_proceso' automáticamente
        $now = date('Y-m-d H:i:s');
        $fechaProgramada = $datos['fecha'];
        
        // Auto-status logic on save
        if ($datos['estado'] === 'pendiente' && strtotime($fechaProgramada) <= strtotime($now)) {
             // Si se programa para ahora o antes, inicia en proceso
             $datos['estado'] = 'en_proceso';
        }

        if ($es_edicion) {
            if ($this->mantenimientoModel->update($id, $datos)) {
                $this->setFlashMessage('success', 'Mantenimiento actualizado exitosamente.');
                $this->logBitacora("Actualizó mantenimiento #{$id}", 'mantenimiento', $id);
            }
        } else {
            if ($newId = $this->mantenimientoModel->create($datos)) {
                $this->setFlashMessage('success', 'Mantenimiento registrado exitosamente.');
                $this->logBitacora("Registró mantenimiento #{$newId}", 'mantenimiento', $newId);
                
                // Obtener equipo para mostrar en notificación
                $equipo = $this->equipoModel->findById($datos['equipo_id']);
                $equipoInfo = $equipo ? "del equipo {$equipo->codigo_inventario}" : "del equipo ID {$datos['equipo_id']}";
                
                // Notificar a admins
                $this->notifyAdmins(
                    "{$_SESSION['username']} programó un mantenimiento {$equipoInfo}",
                    "/mantenimientos/ver/{$newId}"
                );
            }
        }
        header('Location: ' . BASE_URL . 'mantenimientos');
        exit;
    }

    public function completar(int $id)
    {
        $this->restrictTo(['admin', 'tecnico']);
        $mantenimiento = $this->mantenimientoModel->findById($id);
        
        if (!$mantenimiento) {
            $this->setFlashMessage('error', 'Mantenimiento no encontrado.');
            header('Location: ' . BASE_URL . 'mantenimientos');
            exit;
        }

        // Si es técnico, verificar asignación (opcional, pero buena práctica)
        if ($_SESSION['rol'] === 'tecnico' && $mantenimiento->tecnico_id != $_SESSION['user_id']) {
            $this->setFlashMessage('error', 'No tienes permiso para completar este mantenimiento.');
            header('Location: ' . BASE_URL . "mantenimientos/ver/{$id}");
            exit;
        }

        if ($this->mantenimientoModel->update($id, ['estado' => 'completado'])) {
            $this->setFlashMessage('success', 'Mantenimiento marcado como realizado.');
            $this->logBitacora("Completó mantenimiento #{$id}", 'mantenimiento', $id);
        } else {
            $this->setFlashMessage('error', 'Error al actualizar el estado.');
        }

        header('Location: ' . BASE_URL . "mantenimientos/ver/{$id}");
        exit;
    }

    public function eliminar(int $id)
    {
        $this->restrictTo(['admin']);
        if ($this->mantenimientoModel->delete($id)) {
            $this->setFlashMessage('success', 'Mantenimiento eliminado.');
            $this->logBitacora("Eliminó mantenimiento #{$id}", 'mantenimiento', $id);
        } else {
            $this->setFlashMessage('error', 'No se pudo eliminar.');
        }
        header('Location: ' . BASE_URL . 'mantenimientos');
        exit;
    }

    /**
     * Bulk delete maintenances via AJAX
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
            $mantenimiento = $this->mantenimientoModel->findById((int)$id);
            if ($mantenimiento && $this->mantenimientoModel->delete((int)$id)) {
                $deletedCount++;
                $this->logBitacora("Eliminó mantenimiento #{$id} (eliminación masiva)", 'mantenimiento', $id);
            }
        }

        if ($deletedCount > 0) {
            echo json_encode([
                'success' => true,
                'message' => "Se eliminaron {$deletedCount} mantenimiento(s) correctamente.",
                'deleted' => $deletedCount
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar ningún mantenimiento']);
        }
        exit;
    }

    /**
     * Checks all pending/in_process maintenances and updates their status based on time.
     */
    private function updateAutomaticStatuses(int $specificId = null)
    {
        // Fetch candidates: pending or en_proceso
        // This logic could be moved to Model for better separation, but okay here for now.
        $candidates = $this->mantenimientoModel->getPendientes(); 
        
        $now = time();

        foreach ($candidates as $m) {
            if ($specificId && $m->id != $specificId) continue;

            $startTime = strtotime($m->fecha);
            $durationSeconds = ($m->duracion ?? 0) * 60; // duracion is in minutes
            $endTime = $startTime + $durationSeconds;

            $newState = null;

            if ($m->estado === 'pendiente' && $now >= $startTime) {
                // Time to start
                $newState = 'en_proceso';
            }
            
            if ($m->estado === 'en_proceso' && $now >= $endTime && $durationSeconds > 0) {
                // Time is up -> Completed
                $newState = 'completado';
            }

            // Only update if changed
            if ($newState) {
                $this->mantenimientoModel->update($m->id, ['estado' => $newState]);
            }
        }
    }
}

<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Mantenimiento;
use App\Models\Equipo;
use App\Models\Notificacion; // Importar

class MantenimientosController extends Controller
{
    private $mantenimientoModel;
    private $equipoModel;
    private $notificacionModel; // Propiedad

    public function __construct()
    {
        parent::__construct();
        $this->restrictTo(['admin', 'tecnico']);
        $this->mantenimientoModel = new Mantenimiento();
        $this->equipoModel = new Equipo();
        $this->notificacionModel = new Notificacion(); // Inicializar
    }

    public function index()
    {
        if ($_SESSION['rol'] === 'tecnico') {
            $departamentoId = $_SESSION['departamento_id'] ?? 0;
            $mantenimientos = $this->mantenimientoModel->findAllByDepartment($departamentoId);
        } else {
            $mantenimientos = $this->mantenimientoModel->findAllWithDetails();
        }
        
        $this->render('mantenimientos/lista', [
            'titulo' => 'Gestión de Mantenimientos',
            'mantenimientos' => $mantenimientos,
        ]);
    }

    public function ver(int $id)
    {
        $mantenimiento = $this->mantenimientoModel->findById($id);
        if (!$mantenimiento) {
            $this->setFlashMessage('error', 'Mantenimiento no encontrado.');
            header('Location: ' . BASE_URL . 'mantenimientos');
            exit;
        }
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
        
        $this->render('mantenimientos/formulario', [
            'titulo' => 'Registrar Mantenimiento',
            'mantenimiento' => null,
            'equipo_preseleccionado' => $equipo,
            'equipos' => $equipos,
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

        $this->render('mantenimientos/formulario', [
            'titulo' => 'Editar Mantenimiento',
            'mantenimiento' => $mantenimiento,
            'equipos' => $equipos,
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
            'tecnico_id' => $_SESSION['user_id'] ?? null,
        ];

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
}

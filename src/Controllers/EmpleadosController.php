<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Empleado;
use App\Models\Usuario;
use App\Models\Departamento;

class EmpleadosController extends Controller {
    
    private $empleadoModel;
    private $usuarioModel;
    private $departamentoModel;

    public function __construct() {
        parent::__construct(); 
        
        $this->empleadoModel = new Empleado();
        $this->usuarioModel = new Usuario();
        $this->departamentoModel = new Departamento();
        
        $this->restrictTo(['admin']); 
    }

    public function index() {
        $empleados = $this->empleadoModel->findAllWithDetails();

        $this->render('empleados/lista', [
            'titulo' => 'Gestión de Empleados',
            'empleados' => $empleados
        ]);
    }

    public function crear(int $id = null) {
        $empleado = null;
        if ($id) {
            $empleado = $this->empleadoModel->findById($id);
            if (!$empleado) {
                $this->setFlashMessage('error', "Empleado ID #{$id} no encontrado.");
                header('Location: ' . BASE_URL . 'empleados');
                exit;
            }
        }
        
        $usuarios_disponibles = $this->usuarioModel->findAvailable();
        $departamentos = $this->departamentoModel->findAll();

        $this->render('empleados/formulario', [
            'titulo' => ($id ? 'Editar' : 'Registrar Nuevo') . ' Empleado',
            'empleado' => $empleado,
            'usuarios_disponibles' => $usuarios_disponibles,
            'departamentos' => $departamentos
        ]);
    }

    public function ver(int $id) {
        $empleado = $this->empleadoModel->findByIdWithDetails($id);
        
        if (!$empleado) {
            $this->setFlashMessage('error', "Empleado ID #{$id} no encontrado.");
            header('Location: ' . BASE_URL . 'empleados');
            exit;
        }

        $this->render('empleados/ver', [
            'titulo' => 'Perfil de Empleado',
            'empleado' => $empleado
        ]);
    }

    public function editar(int $id) {
        $this->crear($id);
    }

    public function guardar() {
        $this->restrictTo(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'empleados');
            exit;
        }
            
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS);
        $apellido = filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_SPECIAL_CHARS);
        $cedula = filter_input(INPUT_POST, 'cedula', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $usuario_id = filter_input(INPUT_POST, 'usuario_id', FILTER_SANITIZE_NUMBER_INT);
        $departamento_id = filter_input(INPUT_POST, 'departamento_id', FILTER_SANITIZE_NUMBER_INT);

        if (empty($nombre) || empty($apellido) || empty($email) || empty($cedula)) {
            $this->setFlashMessage('error', 'Nombre, Apellido, Cédula y Email son obligatorios.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        $datos = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'cedula' => $cedula,
            'email' => $email,
            'usuario_id' => $usuario_id ?: null,
            'departamento_id' => $departamento_id ?: null
        ];
        
        if ($id) {
            // Actualizar
            if ($this->empleadoModel->update($id, $datos)) {
                
                // --- SYNC LOGIC SGEN ---
                // 1. Unlink this employee from any user currently holding it (to be safe/clean)
                $this->usuarioModel->desvincularEmpleado($id);

                // 2. If a user is selected, link it to this employee and department
                if (!empty($usuario_id)) {
                    $this->usuarioModel->update($usuario_id, [
                        'empleado_id' => $id,
                        'departamento_id' => $departamento_id ?: null
                    ]);
                }
                // -----------------------

                $this->setFlashMessage('success', 'Empleado actualizado correctamente. (Datos sincronizados con Usuario)');
                header('Location: ' . BASE_URL . 'empleados');
                exit;
            }
        } else {
            // Check if duplicate emails are allowed via checkbox
            $permitir_duplicado = isset($_POST['permitir_email_compartido']) && $_POST['permitir_email_compartido'] == '1';
            
            // Validate email uniqueness only if duplicates are NOT allowed  
            if (!$permitir_duplicado) {
                $emailExistente = $this->empleadoModel->findByEmail($email);
                if ($emailExistente) {
                    $this->setFlashMessage('error', 'Error: El email "' . $email . '" ya está registrado. Si deseas permitir correos compartidos, marca la casilla correspondiente.');
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                    exit;
                }
            }
            
            // Crear
            try {
                $newId = $this->empleadoModel->create($datos);
                if ($newId) {
                    
                    // --- SYNC LOGIC SGEN ---
                    if (!empty($usuario_id)) {
                        $this->usuarioModel->update($usuario_id, [
                            'empleado_id' => $newId,
                            'departamento_id' => $departamento_id ?: null
                        ]);
                    }
                    // -----------------------

                    $this->setFlashMessage('success', 'Empleado registrado correctamente. (Datos sincronizados con Usuario)');
                    header('Location: ' . BASE_URL . 'empleados');
                    exit;
                }
            } catch (\PDOException $e) {
                $this->setFlashMessage('error', 'Error inesperado: ' . $e->getMessage());
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
        }
    }
    
    public function eliminar(int $id) {
        $this->restrictTo(['admin']);

        if ($this->empleadoModel->delete($id)) {
            $this->setFlashMessage('success', "Empleado ID #{$id} eliminado.");
        } else {
            $this->setFlashMessage('error', "Error al eliminar el empleado.");
        }
        
        header('Location: ' . BASE_URL . 'empleados');
        exit;
    }

    /**
     * Bulk delete employees via AJAX
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
            if ($this->empleadoModel->delete((int)$id)) {
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            echo json_encode([
                'success' => true,
                'message' => "Se eliminaron {$deletedCount} empleado(s) correctamente.",
                'deleted' => $deletedCount
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar ningún empleado']);
        }
        exit;
    }
    

    /**
     * API para buscar técnicos (JSON)
     * Utilizado por el modal de "Asignar Técnico"
     */
    public function buscarTecnicos() {
        // Asegurar respuesta JSON
        header('Content-Type: application/json');

        try {
            // Verificar sesión (opcional, pero recomendado)
            if (!isset($_SESSION['usuario_id'])) {
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }

            $term = isset($_GET['q']) ? trim($_GET['q']) : '';
            $empleados = $this->empleadoModel->findAllWithDetails();

            $results = [];
            
            // **** DEBUG EXTREMO: Forzando respuesta manual ****
            $results = [
                [
                    'id' => 999,
                    'nombre' => 'PRUEBA',
                    'apellido' => 'CONEXION',
                    'status' => 'available',
                    'active_tickets' => 0,
                    'specialty' => 'SI SE VE ESTO, EL FRONTEND ESTA BIEN'
                ]
            ];
            
            echo json_encode($results);
            exit;
            
            /*
            foreach ($empleados as $emp) {
                // ... código comentado ...
            }
            */

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al buscar técnicos: ' . $e->getMessage()]);
            exit;
        }
    }
}
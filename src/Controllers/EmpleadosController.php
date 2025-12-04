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
            $this->empleadoModel->update($id, $datos);
            $this->setFlashMessage('success', 'Empleado actualizado correctamente.');
            header('Location: ' . BASE_URL . 'empleados');
            exit;
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
                $this->empleadoModel->create($datos);
                $this->setFlashMessage('success', 'Empleado registrado correctamente.');
                header('Location: ' . BASE_URL . 'empleados');
                exit;
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
    
}
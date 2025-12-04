<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
use App\Models\SesionLog; 

class AuthController extends Controller
{
    private $usuarioModel;
    private $sesionLogModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->sesionLogModel = new SesionLog();
    }

    public function login()
    {
        $this->renderSimple('auth/login');
    }

    public function procesar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $usuario = $this->usuarioModel->findByUsername($username);

        if ($usuario && password_verify($password, $usuario->password)) {
            
            // 1. Antes de crear una nueva sesión, cerramos todas las
            //    sesiones colgadas de ESE usuario.
            $this->sesionLogModel->closeAllUserSessions($usuario->id);

            // 2. Ahora creamos la nueva sesión
            session_regenerate_id(true); 

            $_SESSION['user_id']  = $usuario->id;
            $_SESSION['username'] = $usuario->username;
            $_SESSION['rol']      = $usuario->rol;
            $_SESSION['tema']     = $usuario->tema;
            $_SESSION['empleado_id'] = $usuario->empleado_id ?? null;
            
            // 3. Obtener departamento_id
            // Prioridad 1: Asignado directamente al usuario
            // Prioridad 2: Heredado del empleado vinculado
            $departamento_id = $usuario->departamento_id ?? null;

            if (!$departamento_id && $usuario->empleado_id) {
                require_once __DIR__ . '/../Models/Empleado.php';
                $empleadoModel = new \App\Models\Empleado();
                $empleado = $empleadoModel->findById($usuario->empleado_id);
                if ($empleado) {
                    $departamento_id = $empleado->departamento_id;
                }
            }
            $_SESSION['departamento_id'] = $departamento_id;
            
            $log_id = $this->sesionLogModel->createLog(
                $usuario->id, 
                $usuario->username
            );
        
            $_SESSION['session_log_id'] = $log_id;
            
            header('Location: ' . BASE_URL);
            exit;

        } else {
            $this->setFlashMessage('error', 'Credenciales incorrectas. Intente de nuevo.');
            header('Location: '. BASE_URL . 'auth/login');
            exit;
        }
    }

    public function logout()
    {
        $log_id = $_SESSION['session_log_id'] ?? null;
        if ($log_id) {
            $this->sesionLogModel->updateLogoutTime($log_id);
        }
        session_unset();   
        session_destroy(); 
        
        $this->setFlashMessage('success', 'Has cerrado sesión correctamente.');
        header('Location: ' . BASE_URL . 'auth/login');
        exit;
    }
}
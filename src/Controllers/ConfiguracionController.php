<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Usuario;

class ConfiguracionController extends Controller
{
    private $usuarioModel;

    public function __construct()
    {
        parent::__construct(); // Requiere login
        $this->usuarioModel = new Usuario();
    }

    /**
     * Muestra la página de configuración del tema.
     */
    public function index()
    {
        $this->render('configuracion/index', [
            'titulo' => 'Configuración de Tema'
        ]);
    }

    /**
     * Guarda la preferencia de tema del usuario.
     */
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'configuracion');
            exit;
        }

        $validator = new Validator($_POST);
        $validator->check('tema', 'required', 'Debe seleccionar un tema.');
        $validator->check('tema', 'inList', 'El tema no es válido.', ['light', 'dark']);

        if ($validator->fails()) {
            $this->setFlashMessage('error', $validator->getErrors()[array_key_first($validator->getErrors())]);
            header('Location: ' . BASE_URL . 'configuracion');
            exit;
        }

        $nuevoTema = $validator->get('tema');
        $userId = $_SESSION['user_id'];

        // Guardar en la Base de Datos
        if ($this->usuarioModel->update($userId, ['tema' => $nuevoTema])) {
            // Actualizar la Sesión
            $_SESSION['tema'] = $nuevoTema;
            $this->setFlashMessage('success', '¡Tema actualizado correctamente!');
        } else {
            $this->setFlashMessage('error', 'No se pudo guardar el tema.');
        }

        header('Location: ' . BASE_URL . 'configuracion');
        exit;
    }

    /**
     * Cambia la contraseña del usuario (AJAX).
     * @return void
     */
    public function cambiarPassword()
    {
        // Start output buffer to catch any errors
        ob_start();
        
        // Suppress HTML errors for JSON endpoint
        ini_set('display_errors', 0);
        error_reporting(0);
        
        // DEBUG: Log to file
        $debugLog = __DIR__ . '/../../debug_password.txt';
        file_put_contents($debugLog, date('Y-m-d H:i:s') . " - cambiarPassword called\n", FILE_APPEND);
        
        try {
            // Clean buffer before JSON output
            ob_end_clean();
            header('Content-Type: application/json');
            
            file_put_contents($debugLog, date('Y-m-d H:i:s') . " - Headers sent, checking method\n", FILE_APPEND);
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }

            file_put_contents($debugLog, date('Y-m-d H:i:s') . " - Method is POST, parsing input\n", FILE_APPEND);

            $input = json_decode(file_get_contents('php://input'), true);
            file_put_contents($debugLog, date('Y-m-d H:i:s') . " - Input parsed: " . json_encode($input) . "\n", FILE_APPEND);
            
            $currentPassword = $input['password_actual'] ?? '';
            $newPassword = $input['password_nuevo'] ?? '';
            $confirmPassword = $input['password_confirmar'] ?? '';
            
            file_put_contents($debugLog, date('Y-m-d H:i:s') . " - Passwords extracted, validating\n", FILE_APPEND);
            
            // Validaciones
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos.']);
                return;
            }
            
            if ($newPassword !== $confirmPassword) {
                echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
                return;
            }
            
            if (strlen($newPassword) < 8) {
                echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.']);
                return;
            }
            
            if (!preg_match('/[0-9]/', $newPassword)) {
                echo json_encode(['success' => false, 'message' => 'La contraseña debe contener al menos un número.']);
                return;
            }
            
            if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $newPassword)) {
                echo json_encode(['success' => false, 'message' => 'La contraseña debe contener al menos un carácter especial.']);
                return;
            }
            
            file_put_contents($debugLog, date('Y-m-d H:i:s') . " - Validations passed, checking session\n", FILE_APPEND);
            
            $userId = $_SESSION['user_id'] ?? null;
            file_put_contents($debugLog, date('Y-m-d H:i:s') . " - User ID: " . ($userId ?? 'NULL') . "\n", FILE_APPEND);
            
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Sesión no válida.']);
                return;
            }
            
            $user = $this->usuarioModel->find($userId);
            
            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
                return;
            }
            
            // Verificar contraseña actual
            if (!password_verify($currentPassword, $user->password)) {
                echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta.']);
                return;
            }
            
            // Hashear nueva contraseña
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Actualizar en BD
            if ($this->usuarioModel->update($userId, ['password' => $hashedPassword])) {
                echo json_encode(['success' => true, 'message' => '¡Contraseña actualizada correctamente!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña.']);
            }
        } catch (\Exception $e) {
            error_log('Error en cambiarPassword: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
        }
    }
}
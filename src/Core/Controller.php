<?php
namespace App\Core;

use App\Models\Notificacion;
use App\Models\Bitacora;

abstract class Controller {

    private $bitacoraLogger = null;

    public function __construct() {
        if (get_class($this) !== 'App\Controllers\AuthController') {
            $this->checkAuth();
        }
    }

    protected function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }

    protected function restrictTo(array $roles) {
        if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], $roles)) {
            http_response_code(403);
            $this->render('error/403', ['titulo' => 'Acceso Denegado']);
            exit;
        }
    }

    /**
     * Registra una acción en la bitácora.
     */
    protected function logBitacora(string $accion, ?string $enlace_tipo = null, ?int $enlace_id = null) {
        if ($this->bitacoraLogger === null) {
            $this->bitacoraLogger = new Bitacora();
        }

        try {
            $this->bitacoraLogger->createLog(
                $_SESSION['user_id'] ?? 0,
                $_SESSION['username'] ?? 'Sistema',
                $accion,
                $enlace_tipo,
                $enlace_id
            );
        } catch (\Exception $e) {
            error_log('Error al registrar en bitácora: ' . $e->getMessage());
        }
    }
    
    
    protected function render(string $view, array $data = []) {
        
        if (isset($_SESSION['user_id'])) {
            $notificacionModel = new Notificacion();
            $data['unread_count'] = $notificacionModel->countUnread($_SESSION['user_id']);
            $data['notifications_list'] = $notificacionModel->findByUsuario($_SESSION['user_id'], 5);
        } else {
            $data['unread_count'] = 0;
            $data['notifications_list'] = [];
        }

        extract($data);
        
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';

        if (file_exists($viewFile)) {
            // DEBUG: Uncomment to trace view loading
            // echo "<!-- Loading View: $viewFile -->"; 
            require_once __DIR__ . '/../Views/layout/header.php';
            require_once __DIR__ . '/../Views/layout/left-side-menu.php';
            require_once $viewFile;
            require_once __DIR__ . '/../Views/layout/right-side-menu.php';
            require_once __DIR__ . '/../Views/layout/footer.php';
        } else {
            http_response_code(404);
            $this->renderSimple('error/404', ['titulo' => 'Página no encontrada']);
            exit;
        }
    }
    
    protected function renderSimple(string $view, array $data = []) {
        extract($data);
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';

        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("Error fatal: La vista simple '$view' no fue encontrada.");
        }
    }
    
    protected function setFlashMessage(string $type, string $message) {
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    protected function notifyAdmins(string $mensaje, string $enlace = '/')
    {
        if (isset($_SESSION['rol']) && $_SESSION['rol'] !== 'admin') {
            $usuarioModel = new \App\Models\Usuario();
            $notificacionModel = new Notificacion();
            
            $admins = $usuarioModel->findAllAdmins();
            foreach ($admins as $admin) {
                $notificacionModel->createNotification($admin->id, $mensaje, $enlace);
            }
        }
    }

    protected function jsonResponse($data, int $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
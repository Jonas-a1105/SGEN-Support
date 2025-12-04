<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Notificacion; // Importamos el modelo que creamos

class NotificacionesController extends Controller
{
    private $notificacionModel;

    public function __construct()
    {
        parent::__construct(); // Requiere login
        $this->notificacionModel = new Notificacion();
    }

    /**
     * Marca todas las notificaciones del usuario como leídas.
     * Acceso: /notificaciones/marcar-todas-leidas
     */
    public function marcarTodasLeidas()
    {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $this->notificacionModel->markAllAsRead($userId);
        }
        
        // Redirige al usuario a la página anterior
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL;
        header('Location: ' . $referer);
        exit;
    }

    /**
     * (Función futura)
     * Marca una notificación como leída y redirige a su enlace.
     * Acceso: /notificaciones/leer/123
     */
    public function leer(int $id)
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: ' . BASE_URL); // Si no hay sesión, al inicio
            exit;
        }

        // 1. Buscamos la notificación
        $notificacion = $this->notificacionModel->findById($id);

        // 2. Verificamos que sea del usuario actual
        if ($notificacion && $notificacion->usuario_id == $userId) {
            // 3. La marcamos como leída
            $this->notificacionModel->markAsRead($id, $userId);
            // 4. Redirigimos a su enlace original
            header('Location: ' . BASE_URL . ltrim($notificacion->enlace, '/'));
            exit;
        }

        // Si la notificación no existe o no es del usuario, redirigir al dashboard
        header('Location: ' . BASE_URL);
        exit;
    }
}
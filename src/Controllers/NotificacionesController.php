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

    /**
     * API ENDPOINT: Devuelve notificaciones en formato JSON para el frontend.
     * Acceso: /api/notifications
     */
    public function api()
    {
        // Headers al inicio
        header('Content-Type: application/json');
        
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            if (ob_get_length()) ob_clean();
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }

        // Obtener notificaciones desde BD
        $rawNotifs = $this->notificacionModel->findByUsuario($userId, 20); // Traer últimas 20
        
        // Formatear para JS
        $formatted = array_map(function($n) {
            return [
                'id' => $n->id,
                'type' => $this->notificacionModel->detectType($n->mensaje),
                'title' => $this->inferTitle($n->mensaje),
                'message' => $n->mensaje,
                'time' => $this->timeElapsedString($n->created_at),
                'read' => (bool)$n->leido,
                'category' => $this->inferCategory($n->enlace),
                'link' => BASE_URL . ltrim($n->enlace, '/')
            ];
        }, $rawNotifs);

        // Limpiar cualquier output previo (warnings, notices generados por el modelo) para garantizar JSON válido
        if (ob_get_length()) ob_clean();
        echo json_encode($formatted);
        exit;
    }

    /**
     * API ENDPOINT: Marca una notificación como leída vía AJAX.
     * Acceso: POST /api/notifications/mark-read
     */
    public function apiMarcarLeida()
    {
        header('Content-Type: application/json');
        
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit;
        }

        $avg = json_decode(file_get_contents('php://input'), true);
        $id = $avg['id'] ?? null;

        if ($id) {
            $success = $this->notificacionModel->markAsRead($id, $userId);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'error' => 'ID requerido']);
        }
        exit;
    }

    // Helpers privados para formateo
    private function inferTitle($msg) {
        if (stripos($msg, 'ticket') !== false) return 'Actualización de Ticket';
        if (stripos($msg, 'stock') !== false) return 'Alerta de Inventario';
        if (stripos($msg, 'mantenimiento') !== false) return 'Mantenimiento';
        return 'Notificación del Sistema';
    }

    private function inferCategory($link) {
        if (stripos($link, 'soportes') !== false) return 'Soporte';
        if (stripos($link, 'inventario') !== false) return 'Inventario';
        if (stripos($link, 'mantenimientos') !== false) return 'Mantenimiento';
        return 'Sistema';
    }

    private function timeElapsedString($datetime, $full = false) {
        $now = new \DateTime;
        $ago = new \DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'año',
            'm' => 'mes',
            'w' => 'semana',
            'd' => 'día',
            'h' => 'hora',
            'i' => 'minuto',
            's' => 'segundo',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? 'Hace ' . implode(', ', $string) : 'Justo ahora';
    }
}
<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\SesionLog;

class LogController extends Controller
{
    private $logModel;

    public function __construct()
    {
        parent::__construct();
        // Admin, tecnico, consultor, empleado can access logs
        $this->restrictTo(['admin', 'tecnico', 'consultor', 'empleado']);
        $this->logModel = new SesionLog();
    }

    /**
     * Muestra la lista de logs de sesión.
     * Acceso: /logs
     */
    public function index()
    {
        // 1. Pagination & Filters
        $cookiePerPage = isset($_COOKIE['sgen_logs_per_page']) ? (int)$_COOKIE['sgen_logs_per_page'] : 20;
        $perPage = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : $cookiePerPage;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $perPage;

        // Filters from GET
        $filters = [
            'username'    => trim($_GET['username'] ?? ''),
            'fecha_desde' => $_GET['fecha_desde'] ?? '',
            'fecha_hasta' => $_GET['fecha_hasta'] ?? ''
        ];

        // 2. Role-based Restrictions
        // If not admin, enforce user_id filter unless specific logic allows viewing others
        if ($_SESSION['rol'] !== 'admin') {
            // By default, regular users only see their own logs
            $filters['usuario_id'] = $_SESSION['user_id'];
            
            // NOTE: If you previously had logic where 'tecnico' could see their department,
            // you would need to implement complex subquery logic or separate method.
            // For now, aligning with "Own Logs" for safety, unless 'admin'.
            // If you need 'tecnico' to see department, you'd modify findAllPaginated 
            // to accept a Department ID filter or handle it there.
        }

        // 3. Query
        $logs = $this->logModel->findAllPaginated($perPage, $offset, $filters);
        $totalItems = $this->logModel->countAll($filters);
        $totalPages = ceil($totalItems / $perPage);

        $this->render('logs/lista', [
            'titulo' => 'Logs de Sesión',
            'logs'   => $logs,
            'perPage' => $perPage,
            'page'    => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'filters' => $filters
        ]);
    }
}
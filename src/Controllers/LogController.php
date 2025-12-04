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
        if ($_SESSION['rol'] === 'admin') {
            $logs = $this->logModel->findLatestLogs(200);
        } elseif ($_SESSION['rol'] === 'tecnico' || $_SESSION['rol'] === 'consultor') {
            // Tecnico/Consultor sees logs of all employees in their department
            $deptId = $_SESSION['departamento_id'] ?? 0;
            if ($deptId) {
                $logs = $this->logModel->findByDepartmentId($deptId, 200);
            } else {
                // Fallback if no department assigned: show own logs
                $logs = $this->logModel->findByUsuarioId($_SESSION['user_id'], 200);
            }
        } else {
            // Empleado (and others) sees only their own logs
            $logs = $this->logModel->findByUsuarioId($_SESSION['user_id'], 200);
        }

        $this->render('logs/lista', [
            'titulo' => 'Logs de Sesión',
            'logs'   => $logs
        ]);
    }
}
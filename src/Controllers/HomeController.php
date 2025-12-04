<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Soporte;
use App\Models\Equipo;
use App\Models\Departamento;
use App\Models\Mantenimiento;

class HomeController extends Controller
{
    private $soporteModel;
    private $equipoModel;
    private $departamentoModel;
    private $mantenimientoModel;

    public function __construct()
    {
        parent::__construct();
        $this->soporteModel = new Soporte();
        $this->equipoModel = new Equipo();
        $this->departamentoModel = new Departamento();
        $this->mantenimientoModel = new Mantenimiento();
    }

    public function index()
    {
        $rol = $_SESSION['rol'] ?? '';
        $departamentoId = $_SESSION['departamento_id'] ?? 0;

        if ($rol === 'tecnico' || $rol === 'consultor') {
            // Estadísticas filtradas por departamento
            $stats = $this->soporteModel->getDashboardStatsByDepartment($departamentoId);
            
            // Últimos tickets del departamento
            $latestPending = $this->soporteModel->findLatestPendingByDepartment($departamentoId, 5);
            $latestInProcess = $this->soporteModel->findLatestInProcessByDepartment($departamentoId, 5);

            // Estadísticas de equipos del departamento
            $equiposStats = (object)[
                'total' => $this->equipoModel->countByDepartamento($departamentoId),
                'disponible' => $this->equipoModel->countByEstadoAndDepartamento('disponible', $departamentoId),
                'en_uso' => $this->equipoModel->countByEstadoAndDepartamento('en_uso', $departamentoId),
                'en_reparacion' => $this->equipoModel->countByEstadoAndDepartamento('en_reparacion', $departamentoId),
                'fuera_de_servicio' => $this->equipoModel->countByEstadoAndDepartamento('fuera_de_servicio', $departamentoId)
            ];

            // Últimos mantenimientos del departamento
            $ultimosMantenimientos = $this->mantenimientoModel->findLatestByDepartment($departamentoId, 5);
            $totalDepartamentos = $this->departamentoModel->countAll();

            $this->render('dashboard/index', [
                'titulo' => 'Dashboard - Resumen por Departamento',
                'stats' => $stats,
                'latestPending' => $latestPending,
                'latestInProcess' => $latestInProcess,
                'equiposStats' => $equiposStats,
                'ultimosMantenimientos' => $ultimosMantenimientos,
                'totalDepartamentos' => $totalDepartamentos
            ]);

        } else { // Admin
            // Estadísticas generales
            $stats = $this->soporteModel->getDashboardStats();
            $latestPending = $this->soporteModel->findLatestPending(5);
            $latestInProcess = $this->soporteModel->findLatestInProcess(5);
            $ultimosMantenimientos = $this->mantenimientoModel->findLatest(5);
            $totalDepartamentos = $this->departamentoModel->countAll();

            // Estadísticas de equipos
            $equiposStats = (object)[
                'total' => $this->equipoModel->countAll(),
                'disponible' => $this->equipoModel->countByEstado('disponible'),
                'en_uso' => $this->equipoModel->countByEstado('en_uso'),
                'en_reparacion' => $this->equipoModel->countByEstado('en_reparacion'),
                'fuera_de_servicio' => $this->equipoModel->countByEstado('fuera_de_servicio')
            ];

            // Estadísticas adicionales para admin
            $ticketsPorPrioridad = $this->soporteModel->getTicketsByPriority();
            $ticketsPorCategoria = $this->soporteModel->getTicketsByCategory();
            $ticketsPorMes       = $this->soporteModel->getTicketsByMonth();
            $tiempoPromedio      = $this->soporteModel->getAverageResolutionTime();
            $topTecnicos         = $this->soporteModel->getTopTechnicians();

            $this->render('dashboard/index', [
                'titulo' => 'Dashboard - Resumen General',
                'stats' => $stats,
                'latestPending' => $latestPending,
                'latestInProcess' => $latestInProcess,
                'equiposStats' => $equiposStats,
                'ultimosMantenimientos' => $ultimosMantenimientos,
                'totalDepartamentos' => $totalDepartamentos,
                // Variables opcionales para admin
                'ticketsPorPrioridad' => $ticketsPorPrioridad ?? [],
                'ticketsPorCategoria' => $ticketsPorCategoria ?? [],
                'ticketsPorMes'       => $ticketsPorMes ?? [],
                'tiempoPromedio'      => $tiempoPromedio ?? 0,
                'topTecnicos'         => $topTecnicos ?? []
            ]);
        }
    }
}
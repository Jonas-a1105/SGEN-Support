<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Soporte;
use App\Models\Inventario;
use App\Models\Mantenimiento;
use App\Models\Categoria;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReportesController extends Controller
{
    private $soporteModel;
    private $inventarioModel;
    private $mantenimientoModel;

    public function __construct()
    {
        parent::__construct();
        $this->restrictTo(['admin', 'tecnico']);
        $this->soporteModel = new Soporte();
        $this->inventarioModel = new Inventario();
        $this->mantenimientoModel = new Mantenimiento();
    }

    public function index()
    {
        $categorias = (new Categoria())->findAllActive();
        $this->render('reportes/index', [
            'titulo' => 'Panel de Reportes',
            'categorias' => $categorias
        ]);
    }

    public function historial()
    {
        // Datos simulados de historial de descargas
        $historial = [
            (object)[
                'id' => 1,
                'tipo' => 'Soportes',
                'formato' => 'PDF',
                'fecha' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'usuario' => $_SESSION['usuario']['username'] ?? 'admin',
                'filtros' => 'Estado: Todos, Prioridad: Alta'
            ],
            (object)[
                'id' => 2,
                'tipo' => 'Inventario',
                'formato' => 'PDF',
                'fecha' => date('Y-m-d H:i:s', strtotime('-3 hours')),
                'usuario' => $_SESSION['usuario']['username'] ?? 'admin',
                'filtros' => 'Completo'
            ],
            (object)[
                'id' => 3,
                'tipo' => 'Soportes',
                'formato' => 'Excel',
                'fecha' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'usuario' => $_SESSION['usuario']['username'] ?? 'admin',
                'filtros' => 'Fecha: Último mes'
            ],
            (object)[
                'id' => 4,
                'tipo' => 'Mantenimientos',
                'formato' => 'PDF',
                'fecha' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'usuario' => $_SESSION['usuario']['username'] ?? 'admin',
                'filtros' => 'Completo'
            ],
        ];

        $this->render('reportes/historial', [
            'titulo' => 'Historial de Reportes',
            'historial' => $historial
        ]);
    }

    public function soportes()
    {
        $filters = [
            'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
            'fecha_fin'    => $_GET['fecha_fin'] ?? null,
            'estado'       => $_GET['estado'] ?? null,
            'categoria_id' => $_GET['categoria_id'] ?? null,
            'prioridad'    => $_GET['prioridad'] ?? null,
        ];

        $soportes = $this->soporteModel->findAllWithFilters($filters);
        $this->generarPDF('reportes/soportes_pdf', ['soportes' => $soportes, 'filters' => $filters], 'Reporte_Soportes.pdf', 'landscape');
    }

    public function soportes_excel()
    {
        $filters = [
            'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
            'fecha_fin'    => $_GET['fecha_fin'] ?? null,
            'estado'       => $_GET['estado'] ?? null,
            'categoria_id' => $_GET['categoria_id'] ?? null,
            'prioridad'    => $_GET['prioridad'] ?? null,
        ];

        $soportes = $this->soporteModel->findAllWithFilters($filters);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Reporte_Soportes_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Headers
        fputcsv($output, ['ID', 'Fecha', 'Estado', 'Prioridad', 'Categoría', 'Equipo', 'Departamento', 'Técnico', 'Descripción', 'Tiempo (min)', 'Fecha Cierre']);

        foreach ($soportes as $ticket) {
            fputcsv($output, [
                $ticket->id,
                $ticket->fecha,
                ucfirst(str_replace('_', ' ', $ticket->estado)),
                ucfirst($ticket->prioridad),
                $ticket->categoria_nombre ?? 'Sin categoría',
                $ticket->equipo_tipo . ' - ' . $ticket->equipo_serial,
                $ticket->departamento_nombre,
                $ticket->tecnico_asignado ?? 'Sin asignar',
                $ticket->descripcion,
                $ticket->tiempo_atencion_minutos ?? 0,
                $ticket->fecha_cierre ?? 'Pendiente'
            ]);
        }

        fclose($output);
        exit;
    }

    public function inventario()
    {
        $items = $this->inventarioModel->obtenerTodos();
        $this->generarPDF('reportes/inventario_pdf', ['items' => $items], 'Reporte_Inventario.pdf', 'landscape');
    }

    public function mantenimientos()
    {
        $mantenimientos = $this->mantenimientoModel->findAllWithDetails();
        $this->generarPDF('reportes/mantenimientos_pdf', ['mantenimientos' => $mantenimientos], 'Reporte_Mantenimientos.pdf', 'landscape');
    }

    private function generarPDF(string $view, array $data, string $filename, string $orientation = 'portrait')
    {
        // Clean any existing output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        extract($data);
        ob_start();
        include __DIR__ . '/../Views/' . $view . '.php';
        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        // Stream inline (Attachment = false) – opens in new tab
        $dompdf->stream($filename, ['Attachment' => false]);
    }
}
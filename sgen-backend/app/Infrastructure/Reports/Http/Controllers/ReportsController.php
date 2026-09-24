<?php

declare(strict_types=1);

namespace App\Infrastructure\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Barryvdh\DomPDF\Facade\Pdf;

final class ReportsController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Reports/Index', [
            'categories' => $this->getCategories(),
            'departments' => $this->getDepartments(),
        ]);
    }

    public function ticketsPdf(Request $request): Response
    {
        $filters = $request->only(['fecha_inicio', 'fecha_fin', 'estado', 'categoria_id', 'prioridad']);
        
        $tickets = \Illuminate\Support\Facades\DB::table('soportes')
            ->leftJoin('categorias', 'soportes.categoria_id', '=', 'categorias.id')
            ->leftJoin('empleados as tech', 'soportes.empleado_id', '=', 'tech.id')
            ->leftJoin('equipos', 'soportes.equipo_id', '=', 'equipos.id')
            ->leftJoin('departamentos', 'equipos.departamento_id', '=', 'departamentos.id')
            ->select([
                'soportes.*',
                'categorias.nombre as categoria_nombre',
                'tech.nombre as tech_nombre',
                'tech.apellido as tech_apellido',
                'departamentos.nombre as depto_nombre',
                'equipos.codigo_inventario as equipo_codigo',
            ])
            ->when($filters['fecha_inicio'] ?? null, fn($q, $date) => $q->where('soportes.fecha', '>=', $date))
            ->when($filters['fecha_fin'] ?? null, fn($q, $date) => $q->where('soportes.fecha', '<=', $date))
            ->when($filters['estado'] ?? null, fn($q, $estado) => $q->where('soportes.estado', $estado))
            ->when($filters['categoria_id'] ?? null, fn($q, $cat) => $q->where('soportes.categoria_id', $cat))
            ->when($filters['prioridad'] ?? null, fn($q, $prio) => $q->where('soportes.prioridad', $prio))
            ->orderByDesc('soportes.fecha')
            ->get();

        $pdf = Pdf::loadView('reports.tickets-pdf', [
            'tickets' => $tickets,
            'filters' => $filters,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Reporte_Tickets_' . date('Y-m-d') . '.pdf');
    }

    public function ticketsExcel(Request $request): Response
    {
        $filters = $request->only(['fecha_inicio', 'fecha_fin', 'estado', 'categoria_id', 'prioridad']);
        
        $tickets = \Illuminate\Support\Facades\DB::table('soportes')
            ->leftJoin('categorias', 'soportes.categoria_id', '=', 'categorias.id')
            ->leftJoin('empleados as tech', 'soportes.empleado_id', '=', 'tech.id')
            ->leftJoin('equipos', 'soportes.equipo_id', '=', 'equipos.id')
            ->leftJoin('departamentos', 'equipos.departamento_id', '=', 'departamentos.id')
            ->select([
                'soportes.id',
                'soportes.titulo',
                'soportes.fecha',
                'soportes.estado',
                'soportes.prioridad',
                'soportes.fecha_cierre',
                'categorias.nombre as categoria_nombre',
                'tech.nombre as tech_nombre',
                'tech.apellido as tech_apellido',
                'departamentos.nombre as depto_nombre',
                'equipos.codigo_inventario as equipo_codigo',
            ])
            ->when($filters['fecha_inicio'] ?? null, fn($q, $date) => $q->where('soportes.fecha', '>=', $date))
            ->when($filters['fecha_fin'] ?? null, fn($q, $date) => $q->where('soportes.fecha', '<=', $date))
            ->when($filters['estado'] ?? null, fn($q, $estado) => $q->where('soportes.estado', $estado))
            ->when($filters['categoria_id'] ?? null, fn($q, $cat) => $q->where('soportes.categoria_id', $cat))
            ->when($filters['prioridad'] ?? null, fn($q, $prio) => $q->where('soportes.prioridad', $prio))
            ->orderByDesc('soportes.fecha')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="Reporte_Tickets_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($tickets) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($output, ['ID', 'Título', 'Fecha', 'Estado', 'Prioridad', 'Categoría', 'Técnico', 'Departamento', 'Equipo', 'Fecha Cierre']);
            
            foreach ($tickets as $ticket) {
                fputcsv($output, [
                    'T-' . $ticket->id,
                    $ticket->titulo,
                    $ticket->fecha,
                    ucfirst(str_replace('_', ' ', $ticket->estado)),
                    ucfirst($ticket->prioridad),
                    $ticket->categoria_nombre ?? 'Sin categoría',
                    trim(($ticket->tech_nombre ?? '') . ' ' . ($ticket->tech_apellido ?? '')) ?: 'Sin asignar',
                    $ticket->depto_nombre ?? 'Sin departamento',
                    $ticket->equipo_codigo ?? 'N/A',
                    $ticket->fecha_cierre ?? 'Pendiente',
                ]);
            }
            
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function inventoryPdf(): Response
    {
        $items = \Illuminate\Support\Facades\DB::table('inventario_items')
            ->select([
                'inventario_items.*',
                'inventario_items.categoria as categoria_nombre',
            ])
            ->orderBy('inventario_items.codigo')
            ->get();

        $pdf = Pdf::loadView('reports.inventory-pdf', [
            'items' => $items,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Reporte_Inventario_' . date('Y-m-d') . '.pdf');
    }

    public function maintenancePdf(): Response
    {
        $maintenances = \Illuminate\Support\Facades\DB::table('mantenimientos')
            ->leftJoin('equipos', 'mantenimientos.equipo_id', '=', 'equipos.id')
            ->leftJoin('usuarios', 'mantenimientos.tecnico_id', '=', 'usuarios.id')
            ->select([
                'mantenimientos.*',
                'equipos.codigo_inventario as equipo_codigo',
                'equipos.tipo as equipo_tipo',
                'usuarios.username as tecnico_nombre',
            ])
            ->orderByDesc('mantenimientos.fecha')
            ->get();

        $pdf = Pdf::loadView('reports.maintenance-pdf', [
            'maintenances' => $maintenances,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Reporte_Mantenimientos_' . date('Y-m-d') . '.pdf');
    }

    public function performancePdf(): Response
    {
        $kpis = [
            'tickets_total' => \Illuminate\Support\Facades\DB::table('soportes')->count(),
            'tickets_resueltos' => \Illuminate\Support\Facades\DB::table('soportes')->where('estado', 'resuelto')->count(),
            'tickets_pendientes' => \Illuminate\Support\Facades\DB::table('soportes')->where('estado', 'pendiente')->count(),
            'equipos_total' => \Illuminate\Support\Facades\DB::table('equipos')->count(),
            'mantenimientos_completados' => \Illuminate\Support\Facades\DB::table('mantenimientos')->where('estado', 'completado')->count(),
        ];

        $topTechnicians = \Illuminate\Support\Facades\DB::table('soportes')
            ->join('empleados', 'soportes.empleado_id', '=', 'empleados.id')
            ->select('empleados.nombre', 'empleados.apellido', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
            ->where('soportes.estado', 'resuelto')
            ->groupBy('empleados.id', 'empleados.nombre', 'empleados.apellido')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $pdf = Pdf::loadView('reports.performance-pdf', [
            'kpis' => $kpis,
            'topTechnicians' => $topTechnicians,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Reporte_Rendimiento_' . date('Y-m-d') . '.pdf');
    }

    private function getCategories(): array
    {
        return \Illuminate\Support\Facades\DB::table('categorias')
            ->select('id', 'nombre as name')
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->name])
            ->all();
    }

    private function getDepartments(): array
    {
        return \Illuminate\Support\Facades\DB::table('departamentos')
            ->select('id', 'nombre as name')
            ->get()
            ->map(fn($d) => ['id' => $d->id, 'name' => $d->name])
            ->all();
    }
}

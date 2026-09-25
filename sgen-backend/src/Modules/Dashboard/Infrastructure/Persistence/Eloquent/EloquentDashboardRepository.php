<?php

declare(strict_types=1);

namespace Modules\Dashboard\Infrastructure\Persistence\Eloquent;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Dashboard\Domain\Ports\DashboardRepositoryInterface;

final class EloquentDashboardRepository implements DashboardRepositoryInterface
{
    public function getKpiMetrics(): array
    {
        $totalEquipos = (int) DB::table('equipos')->count();
        $pendientes = (int) DB::table('soportes')->where('estado', 'pendiente')->count();
        $enProceso = (int) DB::table('soportes')->where('estado', 'en_proceso')->count();
        $resueltos = (int) DB::table('soportes')->where('estado', 'resuelto')->count();

        return [
            'total_equipos' => $totalEquipos,
            'tickets_pendientes' => $pendientes,
            'tickets_en_proceso' => $enProceso,
            'tickets_resueltos' => $resueltos,
            'cambio_equipos' => '',
            'cambio_pendientes' => '',
            'cambio_proceso' => '',
            'cambio_resueltos' => '',
        ];
    }

    public function getTicketVolumeByYear(int $year): array
    {
        $months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago'];

        $dbCounts = DB::table('soportes')
            ->whereYear('fecha', $year)
            ->selectRaw('EXTRACT(MONTH FROM fecha) as mes, count(*) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->all();

        $values = [];
        foreach ($months as $idx => $m) {
            $monthNum = $idx + 1;
            $values[$idx] = isset($dbCounts[$monthNum]) ? (int) $dbCounts[$monthNum] : 0;
        }

        return [
            'year' => $year,
            'months' => $months,
            'values' => $values,
            'total' => array_sum($values),
            'available_years' => array_map(
                'intval',
                DB::table('soportes')
                    ->selectRaw('EXTRACT(YEAR FROM fecha) as year')
                    ->distinct()
                    ->orderBy('year')
                    ->pluck('year')
                    ->all()
            ),
        ];
    }

    public function getTopCategory(): array
    {
        $category = DB::table('categorias')
            ->select('nombre')
            ->orderBy('id')
            ->first();

        $count = DB::table('soportes')->count();

        return [
            'nombre' => $category ? $category->nombre : 'Sin categorías',
            'cantidad' => $count,
            'porcentaje' => $count > 0 ? 100 : 0,
        ];
    }

    public function getInventoryHealth(): array
    {
        $used = (int) DB::table('equipos')->where('estado', 'en_uso')->count();
        $available = (int) DB::table('equipos')->whereIn('estado', ['disponible', 'nuevo'])->count();
        $repair = (int) DB::table('equipos')->where('estado', 'en_reparacion')->count();
        $down = (int) DB::table('equipos')->where('estado', 'fuera_de_servicio')->count();

        $total = $used + $available + $repair + $down;
        $operativePercentage = $total > 0 ? (int) round((($used + $available) / $total) * 100) : 0;

        return [
            'used' => $used,
            'repair' => $repair,
            'available' => $available,
            'down' => $down,
            'operative_percentage' => $operativePercentage,
        ];
    }

    public function getTechnicianPerformance(): array
    {
        return DB::table('soportes')
            ->join('empleados', 'soportes.empleado_id', '=', 'empleados.id')
            ->select('empleados.nombre', 'empleados.apellido', DB::raw('COUNT(*) as total'))
            ->where('soportes.estado', 'resuelto')
            ->groupBy('empleados.id', 'empleados.nombre', 'empleados.apellido')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($t) => [
                'name' => trim($t->nombre . ' ' . ($t->apellido ?? '')),
                'score' => (int) $t->total,
                'percentage' => 0,
            ])
            ->all();
    }

    public function getRecentActivity(int $limit = 5): array
    {
        $activities = DB::table('soportes')
            ->select('id', 'titulo', 'estado', 'fecha')
            ->orderByDesc('fecha')
            ->limit($limit)
            ->get()
            ->map(fn ($t) => [
                'id' => (int) $t->id,
                'title' => $t->titulo,
                'time_ago' => Carbon::parse($t->fecha)->diffForHumans(),
                'badge' => $t->estado === 'resuelto' ? '+1' : '',
                'type' => $t->estado === 'resuelto' ? 'success' : ($t->estado === 'en_proceso' ? 'info' : 'neutral'),
            ])
            ->all();

        return $activities;
    }

    public function getTicketsByStatus(): array
    {
        $pending = DB::table('soportes')
            ->where('estado', 'pendiente')
            ->select('id', 'titulo', 'prioridad', 'fecha as created_at')
            ->orderByDesc('fecha')
            ->limit(5)
            ->get()
            ->map(fn ($item) => (array) $item)
            ->all();

        $inProcess = DB::table('soportes')
            ->where('estado', 'en_proceso')
            ->select('id', 'titulo', 'prioridad', 'fecha as created_at')
            ->orderByDesc('fecha')
            ->limit(5)
            ->get()
            ->map(fn ($item) => (array) $item)
            ->all();

        return [
            'pending' => $pending,
            'in_process' => $inProcess,
        ];
    }
}
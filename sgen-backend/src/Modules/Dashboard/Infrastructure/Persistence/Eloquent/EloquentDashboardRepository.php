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
        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end = $year === (int) date('Y')
            ? Carbon::now()->endOfDay()
            : Carbon::create($year, 12, 31)->endOfDay();

        if ($end->lessThan($start)) {
            $end = $start;
        }

        $dbCounts = DB::table('soportes')
            ->whereBetween('fecha', [$start, $end])
            ->selectRaw('DATE(fecha) as dia, count(*) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia')
            ->all();

        $labels = [];
        $values = [];
        $cursor = $start->copy();
        while ($cursor->lessThanOrEqualTo($end)) {
            $key = $cursor->toDateString();
            $labels[] = $cursor->format('d/m');
            $values[] = isset($dbCounts[$key]) ? (int) $dbCounts[$key] : 0;
            $cursor->addDay();
        }

        return [
            'year' => $year,
            'months' => $labels,
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
        $total = (int) DB::table('soportes')->whereNull('deleted_at')->count();

        $top = DB::table('soportes')
            ->join('categorias', 'soportes.categoria_id', '=', 'categorias.id')
            ->whereNull('soportes.deleted_at')
            ->select('categorias.nombre as nombre', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('categorias.id', 'categorias.nombre')
            ->orderByDesc('cantidad')
            ->orderBy('categorias.id')
            ->limit(1)
            ->first();

        $cantidad = $top !== null ? (int) $top->cantidad : 0;

        return [
            'nombre' => $top !== null ? (string) $top->nombre : 'Sin categorías',
            'cantidad' => $cantidad,
            'porcentaje' => $total > 0 ? (int) round(($cantidad / $total) * 100) : 0,
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
                'name' => trim($t->nombre.' '.($t->apellido ?? '')),
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

    public function getMyWork(int $userId): array
    {
        // Mi trabajo = lo que yo registré como solicitante, o lo que me asignaron
        // como técnico responsable (mi empleado vinculado).
        $miEmpleadoId = (int) (DB::table('usuarios')->where('id', $userId)->value('empleado_id') ?? 0);

        $filas = DB::table('soportes')
            ->select(['soportes.estado', DB::raw('COUNT(*) as total')])
            ->where(function ($q) use ($userId, $miEmpleadoId) {
                $q->where('soportes.usuario_creacion_id', $userId)
                    ->when($miEmpleadoId > 0, fn ($qq) => $qq->orWhere('soportes.empleado_id', $miEmpleadoId));
            })
            ->groupBy('soportes.estado')
            ->pluck('total', 'estado');

        // Mis activos: custodia vigente asignada al empleado vinculado.

        $misEquipos = $miEmpleadoId > 0
            ? DB::table('custodias')
                ->leftJoin('equipos', 'custodias.equipo_id', '=', 'equipos.id')
                ->where('custodias.empleado_id', $miEmpleadoId)
                ->whereNull('custodias.fecha_fin')
                ->select([
                    'equipos.id',
                    'equipos.codigo_inventario as codigo',
                    DB::raw("trim(equipos.modelo || ' [' || equipos.numero_serie || ']') as nombre"),
                ])
                ->limit(5)
                ->get()
                ->map(static fn ($e) => [
                    'id' => (int) $e->id,
                    'codigo' => (string) $e->codigo,
                    'nombre' => trim((string) $e->nombre),
                ])
                ->all()
            : [];

        // Órdenes laborales que veo: solo si soy el empleado técnico asignado.
        $proximas = $miEmpleadoId > 0
            ? (int) DB::table('mantenimientos')
                ->where('tecnico_id', $miEmpleadoId)
                ->where('estado', '!=', 'completado')
                ->whereNull('deleted_at')
                ->where('fecha', '>=', Carbon::now()->toDateString())
                ->count()
            : 0;

        return [
            'mis_pendientes' => (int) ($filas['pendiente'] ?? 0),
            'mis_en_proceso' => (int) ($filas['en_proceso'] ?? 0),
            'mis_resueltos_mes' => (int) ($filas['resuelto'] ?? 0) + (int) ($filas['cerrado'] ?? 0),
            'mis_equipos' => $misEquipos,
            'proximas_ordenes_mias' => $proximas,
        ];
    }
}

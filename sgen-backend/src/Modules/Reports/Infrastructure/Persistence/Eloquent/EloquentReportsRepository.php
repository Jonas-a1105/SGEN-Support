<?php

declare(strict_types=1);

namespace Modules\Reports\Infrastructure\Persistence\Eloquent;

use Illuminate\Support\Facades\DB;
use Modules\Reports\Domain\Ports\ReportsRepositoryInterface;

final class EloquentReportsRepository implements ReportsRepositoryInterface
{
    public function getFormData(): array
    {
        $categories = DB::table('categorias')
            ->select('id', 'nombre as name')
            ->get()
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
            ->all();

        $departments = DB::table('departamentos')
            ->select('id', 'nombre as name')
            ->get()
            ->map(fn ($d) => ['id' => $d->id, 'name' => $d->name])
            ->all();

        return [
            'categories' => $categories,
            'departments' => $departments,
        ];
    }

    public function getTicketsData(array $filters): array
    {
        return DB::table('soportes')
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
            ->when($filters['fecha_inicio'] ?? null, fn ($q, $date) => $q->where('soportes.fecha', '>=', $date))
            ->when($filters['fecha_fin'] ?? null, fn ($q, $date) => $q->where('soportes.fecha', '<=', $date))
            ->when($filters['estado'] ?? null, fn ($q, $estado) => $q->where('soportes.estado', $estado))
            ->when($filters['categoria_id'] ?? null, fn ($q, $cat) => $q->where('soportes.categoria_id', $cat))
            ->when($filters['prioridad'] ?? null, fn ($q, $prio) => $q->where('soportes.prioridad', $prio))
            ->orderByDesc('soportes.fecha')
            ->get()
            ->all();
    }

    public function getInventoryData(): array
    {
        return DB::table('inventario_items')
            ->select([
                'inventario_items.*',
                'inventario_items.categoria as categoria_nombre',
            ])
            ->orderBy('inventario_items.codigo')
            ->get()
            ->all();
    }

    public function getMaintenanceData(): array
    {
        return DB::table('mantenimientos')
            ->leftJoin('equipos', 'mantenimientos.equipo_id', '=', 'equipos.id')
            ->leftJoin('usuarios', 'mantenimientos.tecnico_id', '=', 'usuarios.id')
            ->select([
                'mantenimientos.*',
                'equipos.codigo_inventario as equipo_codigo',
                'equipos.tipo as equipo_tipo',
                'usuarios.username as tecnico_nombre',
            ])
            ->orderByDesc('mantenimientos.fecha')
            ->get()
            ->all();
    }

    public function getPerformanceData(): array
    {
        $kpis = [
            'tickets_total' => DB::table('soportes')->count(),
            'tickets_resueltos' => DB::table('soportes')->where('estado', 'resuelto')->count(),
            'tickets_pendientes' => DB::table('soportes')->where('estado', 'pendiente')->count(),
            'equipos_total' => DB::table('equipos')->count(),
            'mantenimientos_completados' => DB::table('mantenimientos')->where('estado', 'completado')->count(),
        ];

        $topTechnicians = DB::table('soportes')
            ->join('empleados', 'soportes.empleado_id', '=', 'empleados.id')
            ->select('empleados.nombre', 'empleados.apellido', DB::raw('COUNT(*) as total'))
            ->where('soportes.estado', 'resuelto')
            ->groupBy('empleados.id', 'empleados.nombre', 'empleados.apellido')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->all();

        return [
            'kpis' => $kpis,
            'topTechnicians' => $topTechnicians,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Modules\Maintenance\Infrastructure\Persistence\Eloquent;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Maintenance\Application\DTOs\CreateMaintenanceDTO;
use Modules\Maintenance\Application\DTOs\MaintenanceDetailDTO;
use Modules\Maintenance\Application\DTOs\MaintenanceKpisDTO;
use Modules\Maintenance\Application\DTOs\UpdateMaintenanceDTO;
use Modules\Maintenance\Application\Mappers\MaintenanceListItemMapper;
use Modules\Maintenance\Domain\Ports\MaintenanceRepositoryInterface;

final class EloquentMaintenanceRepository implements MaintenanceRepositoryInterface
{
    public function getKpis(?int $currentUserId = null): MaintenanceKpisDTO
    {
        $pending = (int) DB::table('mantenimientos')
            ->where('estado', 'pendiente')
            ->count();

        $inProcess = (int) DB::table('mantenimientos')
            ->where('estado', 'en_proceso')
            ->count();

        $completed = (int) DB::table('mantenimientos')
            ->where('estado', 'completado')
            ->count();

        $upcoming = (int) DB::table('mantenimientos')
            ->whereNotNull('proxima_fecha')
            ->where('proxima_fecha', '>=', Carbon::now()->toDateString())
            ->where('proxima_fecha', '<=', Carbon::now()->addDays(30)->toDateString())
            ->whereNotIn('estado', ['completado', 'cancelado'])
            ->count();

        $overdue = (int) DB::table('mantenimientos')
            ->where('fecha', '<', Carbon::now())
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->count();

        $total = (int) DB::table('mantenimientos')->count();

        return new MaintenanceKpisDTO(
            pending: $pending,
            inProcess: $inProcess,
            completed: $completed,
            upcoming: $upcoming,
            overdue: $overdue,
            total: $total
        );
    }

    public function listMaintenance(array $filters = []): array
    {
        $query = DB::table('mantenimientos')
            ->leftJoin('equipos', 'mantenimientos.equipo_id', '=', 'equipos.id')
            ->leftJoin('usuarios', 'mantenimientos.tecnico_id', '=', 'usuarios.id')
            ->select([
                'mantenimientos.*',
                'equipos.codigo_inventario as equipo_codigo',
                'equipos.tipo as equipo_tipo',
                'usuarios.username as tecnico_nombre',
            ])
            ->orderByDesc('mantenimientos.fecha');

        if (! empty($filters['estado']) && $filters['estado'] !== 'todos') {
            $query->where('mantenimientos.estado', $filters['estado']);
        }

        if (! empty($filters['tipo']) && $filters['tipo'] !== 'todos') {
            $query->where('mantenimientos.tipo_mantenimiento', $filters['tipo']);
        }

        if (! empty($filters['search'])) {
            $search = '%'.trim($filters['search']).'%';
            $query->where(function ($q) use ($search) {
                $q->where('mantenimientos.descripcion', 'ILIKE', $search)
                    ->orWhere('equipos.codigo_inventario', 'ILIKE', $search)
                    ->orWhere('equipos.tipo', 'ILIKE', $search);
            });
        }

        $records = $query->limit(100)->get();

        return $records
            ->map(fn ($row) => MaintenanceListItemMapper::fromDatabaseRow($row))
            ->all();
    }

    public function findById(int $id): ?MaintenanceDetailDTO
    {
        $record = DB::table('mantenimientos')
            ->leftJoin('equipos', 'mantenimientos.equipo_id', '=', 'equipos.id')
            ->leftJoin('departamentos', 'equipos.departamento_id', '=', 'departamentos.id')
            ->leftJoin('empleados as custodio', 'equipos.empleado_id', '=', 'custodio.id')
            ->leftJoin('usuarios', 'mantenimientos.tecnico_id', '=', 'usuarios.id')
            ->leftJoin('empleados as emp_user', 'usuarios.id', '=', 'emp_user.usuario_id')
            ->leftJoin('empleados as emp_direct', 'mantenimientos.tecnico_id', '=', 'emp_direct.id')
            ->where('mantenimientos.id', $id)
            ->select([
                'mantenimientos.*',
                'equipos.codigo_inventario as equipo_codigo',
                'equipos.tipo as equipo_tipo',
                'equipos.marca as equipo_marca',
                'equipos.modelo as equipo_modelo',
                'equipos.numero_serie as equipo_serial',
                'equipos.estado as equipo_estado',
                'equipos.id as equipo_id_real',
                'departamentos.nombre as equipo_departamento',
                'departamentos.id as equipo_departamento_id',
                DB::raw("TRIM(CONCAT(custodio.nombre, ' ', COALESCE(custodio.apellido, ''))) as equipo_custodio"),
                'custodio.id as equipo_custodio_id',
                'usuarios.username as tecnico_username',
                DB::raw("COALESCE(
                    NULLIF(TRIM(CONCAT(emp_direct.nombre, ' ', COALESCE(emp_direct.apellido, ''))), ''),
                    NULLIF(TRIM(CONCAT(emp_user.nombre, ' ', COALESCE(emp_user.apellido, ''))), ''),
                    usuarios.username
                ) as tecnico_completo"),
            ])
            ->first();

        if (! $record) {
            return null;
        }

        $duracion = null;
        if (isset($record->duracion) && $record->duracion) {
            $duracion = (int) $record->duracion;
        } elseif ($record->observaciones && preg_match('/Duración estimada:\s*(\d+)\s*min/i', (string) $record->observaciones, $m)) {
            $duracion = (int) $m[1];
        }

        $fecha = Carbon::parse($record->fecha);
        $isOverdue = $fecha->isPast() && ! in_array($record->estado, ['completado', 'cancelado'], true);

        $historial = DB::table('mantenimientos')
            ->leftJoin('usuarios', 'mantenimientos.tecnico_id', '=', 'usuarios.id')
            ->leftJoin('empleados as emp_user', 'usuarios.id', '=', 'emp_user.usuario_id')
            ->leftJoin('empleados as emp_direct', 'mantenimientos.tecnico_id', '=', 'emp_direct.id')
            ->where('mantenimientos.equipo_id', $record->equipo_id)
            ->where('mantenimientos.id', '!=', $id)
            ->orderByDesc('mantenimientos.fecha')
            ->limit(5)
            ->select([
                'mantenimientos.id',
                'mantenimientos.fecha',
                'mantenimientos.tipo_mantenimiento',
                'mantenimientos.estado',
                'mantenimientos.costo',
                'mantenimientos.descripcion',
                DB::raw("COALESCE(
                    NULLIF(TRIM(CONCAT(emp_direct.nombre, ' ', COALESCE(emp_direct.apellido, ''))), ''),
                    NULLIF(TRIM(CONCAT(emp_user.nombre, ' ', COALESCE(emp_user.apellido, ''))), ''),
                    usuarios.username
                ) as tecnico_nombre"),
            ])
            ->get()
            ->map(fn ($h) => [
                'id' => (int) $h->id,
                'fecha' => (string) $h->fecha,
                'tipoMantenimiento' => (string) $h->tipo_mantenimiento,
                'estado' => (string) $h->estado,
                'costo' => $h->costo !== null ? (float) $h->costo : null,
                'descripcion' => (string) $h->descripcion,
                'tecnicoNombre' => $h->tecnico_nombre ?? 'Sin asignar',
            ])
            ->all();

        return new MaintenanceDetailDTO(
            id: (int) $record->id,
            equipoId: (int) $record->equipo_id,
            fecha: (string) $record->fecha,
            tipoMantenimiento: (string) $record->tipo_mantenimiento,
            estado: (string) $record->estado,
            descripcion: (string) $record->descripcion,
            frecuencia: (string) $record->frecuencia,
            proximaFecha: $record->proxima_fecha,
            costo: $record->costo !== null ? (float) $record->costo : null,
            tecnicoId: $record->tecnico_id ? (int) $record->tecnico_id : null,
            tecnicoNombre: $record->tecnico_completo ?? $record->tecnico_username,
            realizadoPor: $record->realizado_por,
            observaciones: $record->observaciones,
            checklist: $record->checklist ? (is_array($record->checklist) ? $record->checklist : json_decode($record->checklist, true)) : null,
            duracion: $duracion,
            createdAt: $record->created_at,
            updatedAt: $record->updated_at,
            equipo: [
                'id' => (int) ($record->equipo_id_real ?? $record->equipo_id),
                'codigo' => $record->equipo_codigo ?? 'S/C',
                'tipo' => $record->equipo_tipo ?? 'Equipo',
                'marca' => $record->equipo_marca ?? 'N/A',
                'modelo' => $record->equipo_modelo ?? 'N/A',
                'serial' => $record->equipo_serial ?? 'N/A',
                'estado' => $record->equipo_estado ?? 'disponible',
                'departamento' => $record->equipo_departamento ?? 'Sin departamento',
                'departamentoId' => $record->equipo_departamento_id ? (int) $record->equipo_departamento_id : null,
                'custodio' => $record->equipo_custodio ?? 'Sin custodio',
                'custodioId' => $record->equipo_custodio_id ? (int) $record->equipo_custodio_id : null,
            ],
            historialEquipo: $historial,
            isOverdue: $isOverdue
        );
    }

    public function create(CreateMaintenanceDTO $dto, ?int $userId = null): int
    {
        $estado = 'pendiente';
        $fechaProgramada = Carbon::parse($dto->fecha);

        if ($fechaProgramada <= Carbon::now()) {
            $estado = 'en_proceso';
        }

        $observaciones = $dto->observaciones;
        if ($dto->duracion !== null) {
            $duracionNote = "Duración estimada: {$dto->duracion} min";
            $observaciones = $observaciones ? "{$observaciones} | {$duracionNote}" : $duracionNote;
        }

        return (int) DB::table('mantenimientos')->insertGetId([
            'equipo_id' => $dto->equipoId,
            'fecha' => $dto->fecha,
            'tipo_mantenimiento' => $dto->tipoMantenimiento,
            'estado' => $estado,
            'descripcion' => $dto->descripcion,
            'frecuencia' => $dto->frecuencia,
            'proxima_fecha' => $dto->proximaFecha,
            'costo' => $dto->costo ?? 0,
            'tecnico_id' => $dto->tecnicoId ?? $userId,
            'observaciones' => $observaciones,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    public function update(int $id, UpdateMaintenanceDTO $dto): bool
    {
        $payload = ['updated_at' => Carbon::now()];

        if ($dto->fecha !== null) {
            $payload['fecha'] = $dto->fecha;
        }
        if ($dto->tipoMantenimiento !== null) {
            $payload['tipo_mantenimiento'] = $dto->tipoMantenimiento;
        }
        if ($dto->estado !== null) {
            $payload['estado'] = $dto->estado;
        }
        if ($dto->descripcion !== null) {
            $payload['descripcion'] = $dto->descripcion;
        }
        if ($dto->frecuencia !== null) {
            $payload['frecuencia'] = $dto->frecuencia;
        }
        if ($dto->proximaFecha !== null) {
            $payload['proxima_fecha'] = $dto->proximaFecha;
        }
        if ($dto->costo !== null) {
            $payload['costo'] = $dto->costo;
        }
        if ($dto->tecnicoId !== null) {
            $payload['tecnico_id'] = $dto->tecnicoId;
        }
        if ($dto->observaciones !== null) {
            $payload['observaciones'] = $dto->observaciones;
        }

        return DB::table('mantenimientos')->where('id', $id)->update($payload) > 0;
    }

    public function delete(int $id): bool
    {
        return DB::table('mantenimientos')->where('id', $id)->delete() > 0;
    }

    public function deleteBulk(array $ids): int
    {
        return DB::table('mantenimientos')->whereIn('id', $ids)->delete();
    }

    public function complete(int $id, ?string $observations = null, ?float $cost = null, ?string $garantiaHasta = null): bool
    {
        $maintenance = DB::table('mantenimientos')->where('id', $id)->first();

        if (! $maintenance) {
            return false;
        }

        $payload = [
            'estado' => 'completado',
            'updated_at' => Carbon::now(),
        ];

        if ($observations !== null) {
            $payload['observaciones'] = $observations;
        }
        if ($cost !== null) {
            $payload['costo'] = $cost;
        }
        if ($garantiaHasta !== null) {
            $payload['garantia_hasta'] = $garantiaHasta;
        }

        if ($maintenance->frecuencia !== 'unica') {
            $months = match ($maintenance->frecuencia) {
                'mensual' => 1,
                'trimestral' => 3,
                'semestral' => 6,
                'anual' => 12,
                default => 0,
            };

            if ($months > 0) {
                $payload['proxima_fecha'] = Carbon::now()->addMonths($months)->toDateString();
            }
        }

        return DB::table('mantenimientos')->where('id', $id)->update($payload) > 0;
    }

    public function postpone(int $id, string $newDate): bool
    {
        return DB::table('mantenimientos')
            ->where('id', $id)
            ->update([
                'estado' => 'pospuesto',
                'fecha' => $newDate,
                'updated_at' => Carbon::now(),
            ]) > 0;
    }

    public function addMaterial(int $mantenimientoId, int $itemId, int $cantidad, int $userId): bool
    {
        return (bool) DB::table('mantenimiento_materiales')->insert([
            'mantenimiento_id' => $mantenimientoId,
            'item_id' => $itemId,
            'cantidad' => $cantidad,
            'costo_unitario' => (float) (DB::table('inventario_items')->where('id', $itemId)->value('valor_compra') ?? 0),
            'created_at' => now(),
        ]);
    }

    public function getMateriales(int $mantenimientoId): array
    {
        return DB::table('mantenimiento_materiales')
            ->join('inventario_items', 'mantenimiento_materiales.item_id', '=', 'inventario_items.id')
            ->where('mantenimiento_materiales.mantenimiento_id', $mantenimientoId)
            ->select([
                'mantenimiento_materiales.id',
                'mantenimiento_materiales.item_id',
                'mantenimiento_materiales.cantidad',
                'mantenimiento_materiales.costo_unitario',
                'mantenimiento_materiales.created_at as fecha',
                'inventario_items.codigo as item_codigo',
                'inventario_items.nombre as item_nombre',
            ])
            ->orderBy('mantenimiento_materiales.id')
            ->get()
            ->map(fn ($m) => [
                'id' => (int) $m->id,
                'item_id' => (int) $m->item_id,
                'item_codigo' => (string) $m->item_codigo,
                'item_nombre' => (string) $m->item_nombre,
                'cantidad' => (int) $m->cantidad,
                'costo_unitario' => (float) $m->costo_unitario,
                'costo_total' => round($m->costo_unitario * $m->cantidad, 2),
                'fecha' => (string) $m->fecha,
            ])
            ->all();
    }

    public function cancel(int $id, string $reason): bool
    {
        return DB::table('mantenimientos')
            ->where('id', $id)
            ->update([
                'estado' => 'cancelado',
                'observaciones' => $reason,
                'updated_at' => Carbon::now(),
            ]) > 0;
    }

    public function getUpcoming(int $days = 30): array
    {
        $records = DB::table('mantenimientos')
            ->leftJoin('equipos', 'mantenimientos.equipo_id', '=', 'equipos.id')
            ->whereNotNull('mantenimientos.proxima_fecha')
            ->where('mantenimientos.proxima_fecha', '>=', Carbon::now()->toDateString())
            ->where('mantenimientos.proxima_fecha', '<=', Carbon::now()->addDays($days)->toDateString())
            ->whereNotIn('mantenimientos.estado', ['completado', 'cancelado'])
            ->select([
                'mantenimientos.*',
                'equipos.codigo_inventario as equipo_codigo',
                'equipos.tipo as equipo_tipo',
            ])
            ->orderBy('mantenimientos.proxima_fecha')
            ->get();

        return $records
            ->map(fn ($row) => MaintenanceListItemMapper::fromDatabaseRow($row))
            ->all();
    }

    public function getFormOptions(): array
    {
        $equipments = DB::table('equipos')
            ->leftJoin('departamentos', 'equipos.departamento_id', '=', 'departamentos.id')
            ->select([
                'equipos.id',
                'equipos.codigo_inventario as code',
                'equipos.tipo as type',
                'equipos.modelo as model',
                'departamentos.nombre as department',
            ])
            ->limit(100)
            ->get()
            ->map(fn ($eq) => [
                'id' => $eq->id,
                'code' => $eq->code,
                'type' => ucfirst($eq->type),
                'model' => $eq->model ?? 'Genérico',
                'department' => $eq->department ?? 'Sin departamento',
            ])
            ->all();

        $technicians = DB::table('empleados')
            ->select('id', 'nombre', 'apellido', 'email')
            ->where('rol', 'tecnico')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'name' => trim($e->nombre.' '.($e->apellido ?? '')),
                'email' => $e->email ?? '',
            ])
            ->all();

        $inventoryItems = DB::table('inventario_items')
            ->select('id', 'codigo', 'nombre', 'stock_actual', 'unidad_medida', 'valor_compra')
            ->where('stock_actual', '>', 0)
            ->orderBy('nombre')
            ->get()
            ->map(fn ($item) => [
                'id' => (int) $item->id,
                'codigo' => (string) $item->codigo,
                'nombre' => (string) $item->nombre,
                'stock_actual' => (int) $item->stock_actual,
                'unidad_medida' => (string) ($item->unidad_medida ?? 'uds'),
                'valor_compra' => (float) ($item->valor_compra ?? 0),
            ])
            ->all();

        return [
            'equipments' => $equipments,
            'technicians' => $technicians,
            'inventory_items' => $inventoryItems,
            'types' => [
                ['value' => 'preventivo', 'label' => 'Preventivo'],
                ['value' => 'correctivo', 'label' => 'Correctivo'],
                ['value' => 'predictivo', 'label' => 'Predictivo'],
            ],
            'frequencies' => [
                ['value' => 'unica', 'label' => 'Única vez'],
                ['value' => 'mensual', 'label' => 'Mensual'],
                ['value' => 'trimestral', 'label' => 'Trimestral'],
                ['value' => 'semestral', 'label' => 'Semestral'],
                ['value' => 'anual', 'label' => 'Anual'],
            ],
        ];
    }
}

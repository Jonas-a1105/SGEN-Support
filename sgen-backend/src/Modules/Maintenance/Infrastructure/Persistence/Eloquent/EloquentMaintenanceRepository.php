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

        if (!empty($filters['estado']) && $filters['estado'] !== 'todos') {
            $query->where('mantenimientos.estado', $filters['estado']);
        }

        if (!empty($filters['tipo']) && $filters['tipo'] !== 'todos') {
            $query->where('mantenimientos.tipo_mantenimiento', $filters['tipo']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
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
            ->leftJoin('usuarios', 'mantenimientos.tecnico_id', '=', 'usuarios.id')
            ->leftJoin('empleados', 'usuarios.id', '=', 'empleados.usuario_id')
            ->where('mantenimientos.id', $id)
            ->select([
                'mantenimientos.*',
                'equipos.codigo_inventario as equipo_codigo',
                'equipos.tipo as equipo_tipo',
                'equipos.marca as equipo_marca',
                'equipos.modelo as equipo_modelo',
                'equipos.numero_serie as equipo_serial',
                'equipos.id as equipo_id_real',
                'usuarios.username as tecnico_nombre',
                DB::raw("CONCAT(empleados.nombre, ' ', empleados.apellido) as tecnico_completo"),
            ])
            ->first();

        if (!$record) {
            return null;
        }

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
            tecnicoId: $record->tecnico_id,
            tecnicoNombre: $record->tecnico_completo ?? $record->tecnico_nombre,
            realizadoPor: $record->realizado_por,
            observaciones: $record->observaciones,
            checklist: $record->checklist ? json_decode($record->checklist, true) : null,
            duracion: $record->duracion ? (int) $record->duracion : null,
            createdAt: $record->created_at,
            updatedAt: $record->updated_at,
            equipo: [
                'id' => (int) $record->equipo_id_real,
                'codigo' => $record->equipo_codigo,
                'tipo' => $record->equipo_tipo,
                'marca' => $record->equipo_marca,
                'modelo' => $record->equipo_modelo,
                'serial' => $record->equipo_serial,
            ]
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

    public function complete(int $id, ?string $observations = null, ?float $cost = null): bool
    {
        $maintenance = DB::table('mantenimientos')->where('id', $id)->first();
        
        if (!$maintenance) {
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

        if ($maintenance->frecuencia !== 'unica') {
            $months = match($maintenance->frecuencia) {
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
                'name' => trim($e->nombre . ' ' . ($e->apellido ?? '')),
                'email' => $e->email ?? '',
            ])
            ->all();

        return [
            'equipments' => $equipments,
            'technicians' => $technicians,
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

<?php

declare(strict_types=1);

namespace Modules\Equipment\Infrastructure\Persistence\Eloquent;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Equipment\Application\DTOs\CreateEquipmentDTO;
use Modules\Equipment\Application\DTOs\EquipmentDetailDTO;
use Modules\Equipment\Application\DTOs\EquipmentKpisDTO;
use Modules\Equipment\Application\DTOs\EquipmentListItemDTO;
use Modules\Equipment\Application\DTOs\RegisterEquipmentDTO;
use Modules\Equipment\Application\DTOs\TransferEquipmentDTO;
use Modules\Equipment\Application\DTOs\UpdateEquipmentDTO;
use Modules\Equipment\Application\Mappers\EquipmentDetailMapper;
use Modules\Equipment\Application\Mappers\EquipmentListItemMapper;
use Modules\Equipment\Domain\Enums\EquipmentStatus;
use Modules\Equipment\Domain\Exceptions\EquipmentNotFoundException;
use Modules\Equipment\Domain\Ports\EquipmentRepositoryInterface;

final class EloquentEquipmentRepository implements EquipmentRepositoryInterface
{
    public function getKpis(): EquipmentKpisDTO
    {
        $totalActivos = (int) DB::table('equipos')->count();
        $enUso = (int) DB::table('equipos')->where('estado', 'en_uso')->count();
        $disponibles = (int) DB::table('equipos')->whereIn('estado', ['disponible', 'nuevo', 'en_reserva'])->count();
        $enReparacion = (int) DB::table('equipos')->where('estado', 'en_reparacion')->count();
        $fueraServicio = (int) DB::table('equipos')->whereIn('estado', ['fuera_de_servicio', 'baja'])->count();
        $operativos = max(0, $totalActivos - $enReparacion - $fueraServicio);

        return new EquipmentKpisDTO(
            totalActivos: $totalActivos,
            operativos: $operativos,
            enReparacion: $enReparacion,
            fueraServicio: $fueraServicio,
            enUso: $enUso,
            disponibles: $disponibles,
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return EquipmentListItemDTO[]
     */
    public function list(array $filters = []): array
    {
        // La papelera queda fuera del listado operativo (restauración vía /papelera).
        $query = DB::table('equipos')
            ->whereNull('equipos.deleted_at');

        // Visibilidad por rol: un operador solo lee su departamento; tech/admin/consultor global.
        $query = \App\Support\Visibility\VisibilityScope::applyToDepartamentos($query, 'equipos.departamento_id')
            ->leftJoin('departamentos', 'equipos.departamento_id', '=', 'departamentos.id')
            ->leftJoin('empleados', 'equipos.empleado_id', '=', 'empleados.id')
            ->select([
                'equipos.*',
                'departamentos.nombre as departamento_nombre',
                'empleados.nombre as empleado_nombre',
                'empleados.apellido as empleado_apellido',
            ])
            ->orderBy('equipos.id', 'desc');

        // Filtro por estado
        if (! empty($filters['estado']) && $filters['estado'] !== 'all') {
            $statusEnum = EquipmentStatus::tryFrom($filters['estado']) ?? EquipmentStatus::fromLabel($filters['estado']);
            $query->where('equipos.estado', $statusEnum->value);
        }

        // Filtro por departamento
        if (! empty($filters['departamento_id'])) {
            $query->where('equipos.departamento_id', (int) $filters['departamento_id']);
        }

        // Filtro por tipo
        if (! empty($filters['tipo'])) {
            $query->where('equipos.tipo', 'ilike', '%'.$filters['tipo'].'%');
        }

        // BÃºsqueda general
        if (! empty($filters['search'])) {
            $term = '%'.trim((string) $filters['search']).'%';
            $query->where(function ($q) use ($term) {
                $q->where('equipos.codigo_inventario', 'ilike', $term)
                    ->orWhere('equipos.numero_serie', 'ilike', $term)
                    ->orWhere('equipos.tipo', 'ilike', $term)
                    ->orWhere('equipos.marca', 'ilike', $term)
                    ->orWhere('equipos.modelo', 'ilike', $term)
                    ->orWhere('equipos.ubicacion_fisica', 'ilike', $term)
                    ->orWhere('departamentos.nombre', 'ilike', $term)
                    ->orWhere('empleados.nombre', 'ilike', $term)
                    ->orWhere('empleados.apellido', 'ilike', $term);
            });
        }

        return $query->get()
            ->map(fn ($row) => EquipmentListItemMapper::fromRow($row))
            ->all();
    }

    public function findById(int $id): ?EquipmentDetailDTO
    {
        $row = DB::table('equipos')
            ->leftJoin('departamentos', 'equipos.departamento_id', '=', 'departamentos.id')
            ->leftJoin('empleados', 'equipos.empleado_id', '=', 'empleados.id')
            ->leftJoin('usuarios', 'equipos.responsable_baja_id', '=', 'usuarios.id')
            ->select([
                'equipos.*',
                'departamentos.nombre as departamento_nombre',
                'empleados.nombre as empleado_nombre',
                'empleados.apellido as empleado_apellido',
                'usuarios.username as responsable_nombre',
            ])
            ->where('equipos.id', $id)
            ->first();

        if ($row === null) {
            return null;
        }

        return EquipmentDetailMapper::fromRow($row);
    }

    public function getCompleteDetail(int $id): ?EquipmentDetailDTO
    {
        $row = DB::table('equipos')
            ->leftJoin('usuarios', 'equipos.responsable_baja_id', '=', 'usuarios.id')
            ->leftJoin('departamentos', 'equipos.departamento_id', '=', 'departamentos.id')
            ->leftJoin('empleados', 'equipos.empleado_id', '=', 'empleados.id')
            ->select([
                'equipos.*',
                'departamentos.nombre as departamento_nombre',
                'empleados.nombre as empleado_nombre',
                'empleados.apellido as empleado_apellido',
                'usuarios.username as responsable_nombre',
            ])
            ->where('equipos.id', $id)
            ->first();

        if ($row === null) {
            return null;
        }

        // Warranty calculation
        $warrantyPercent = 0;
        $warrantyStatus = 'expired';
        $warrantyRemaining = null;

        if (! empty($row->fecha_compra) && ! empty($row->garantia)) {
            $start = Carbon::parse($row->fecha_compra)->timestamp;
            $end = Carbon::parse($row->garantia)->timestamp;
            $now = Carbon::now()->timestamp;
            $total = $end - $start;
            $elapsed = $now - $start;

            if ($total > 0) {
                $warrantyPercent = (int) max(0, min(100, (($total - $elapsed) / $total) * 100));
                if ($now < $end) {
                    $warrantyStatus = $warrantyPercent > 33 ? 'active' : 'warning';
                    $daysRemaining = (int) ceil(($end - $now) / 86400);
                    $warrantyRemaining = $daysRemaining > 365
                        ? round($daysRemaining / 365, 1).' aÃ±os'
                        : round($daysRemaining / 30).' meses';
                }
            }
        }

        // Associated support tickets
        $tickets = DB::table('soportes')
            ->select(['id', 'titulo', 'descripcion', 'estado', 'prioridad', 'fecha'])
            ->where('equipo_id', $id)
            ->orderByDesc('fecha')
            ->limit(20)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => (int) $s->id,
                    'titulo' => (string) ($s->titulo ?? 'Ticket #'.$s->id),
                    'descripcion' => (string) ($s->descripcion ?? ''),
                    'estado' => (string) ($s->estado ?? 'pendiente'),
                    'prioridad' => (string) ($s->prioridad ?? 'media'),
                    'fecha' => isset($s->fecha) ? Carbon::parse($s->fecha)->format('d/m/Y H:i') : null,
                ];
            })
            ->all();

        // Associated maintenance orders
        $maintenances = DB::table('mantenimientos')
            ->select(['id', 'tipo_mantenimiento', 'estado', 'descripcion', 'costo', 'realizado_por', 'fecha', 'proxima_fecha'])
            ->where('equipo_id', $id)
            ->orderByDesc('fecha')
            ->limit(20)
            ->get()
            ->map(function ($m) {
                return [
                    'id' => (int) $m->id,
                    'tipo' => (string) ($m->tipo_mantenimiento ?? 'preventivo'),
                    'estado' => (string) ($m->estado ?? 'completado'),
                    'descripcion' => (string) ($m->descripcion ?? ''),
                    'costo' => (float) ($m->costo ?? 0),
                    'realizadoPor' => (string) ($m->realizado_por ?? 'TÃ©cnico de soporte'),
                    'fecha' => isset($m->fecha) ? Carbon::parse($m->fecha)->format('d/m/Y') : null,
                    'proximaFecha' => isset($m->proxima_fecha) ? Carbon::parse($m->proxima_fecha)->format('d/m/Y') : null,
                ];
            })
            ->all();

        // Department options
        $departamentos = DB::table('departamentos')
            ->select(['id', 'nombre'])
            ->orderBy('nombre')
            ->get()
            ->map(fn ($d) => ['id' => (int) $d->id, 'nombre' => (string) $d->nombre])
            ->all();

        // Employee options
        $empleados = DB::table('empleados')
            ->select(['id', 'nombre', 'apellido', 'cargo'])
            ->whereNull('deleted_at')
            ->orderBy('nombre')
            ->get()
            ->map(fn ($e) => [
                'id' => (int) $e->id,
                'nombre' => trim($e->nombre.' '.($e->apellido ?? '')),
                'cargo' => (string) ($e->cargo ?? 'Personal'),
            ])
            ->all();

        $baseDto = EquipmentDetailMapper::fromRow($row);

        return new EquipmentDetailDTO(
            id: $baseDto->id,
            inventoryCode: $baseDto->inventoryCode,
            serialNumber: $baseDto->serialNumber,
            name: $baseDto->name,
            type: $baseDto->type,
            brand: $baseDto->brand,
            model: $baseDto->model,
            status: $baseDto->status,
            rawStatus: $baseDto->rawStatus,
            departmentId: $baseDto->departmentId,
            departmentName: $baseDto->departmentName,
            employeeId: $baseDto->employeeId,
            employeeName: $baseDto->employeeName,
            physicalLocation: $baseDto->physicalLocation,
            processor: $baseDto->processor,
            ram: $baseDto->ram,
            storage: $baseDto->storage,
            os: $baseDto->os,
            ipAddress: $baseDto->ipAddress,
            driver: $baseDto->driver,
            toner: $baseDto->toner,
            purchaseDate: $baseDto->purchaseDate,
            supplier: $baseDto->supplier,
            warranty: $baseDto->warranty,
            purchaseValue: $baseDto->purchaseValue,
            warrantyPercent: $warrantyPercent,
            warrantyStatus: $warrantyStatus,
            warrantyRemaining: $warrantyRemaining,
            tickets: $tickets,
            maintenances: $maintenances,
            departamentos: $departamentos,
            empleados: $empleados,
            custodiaActual: $this->currentCustody($id),
            custodias: $this->custodyHistory($id),
        );
    }

    public function create(CreateEquipmentDTO $dto): int
    {
        if ($dto->status === 'de_baja') {
            throw new \DomainException('Un equipo no puede nacer dado de baja; regístelo y luego ejecute la baja formal.');
        }

        $statusEnum = EquipmentStatus::tryFrom($dto->status) ?? EquipmentStatus::fromLabel($dto->status);

        $now = Carbon::now();

        // Determinar cÃ³digo de inventario si no fue provisto
        $code = trim($dto->inventoryCode);
        if ($code === '') {
            $maxId = (int) DB::table('equipos')->max('id');
            $code = sprintf('%05d', $maxId + 1);
        }

        // Domain-level duplicate check for inventory code and serial number
        $duplicate = DB::table('equipos')
            ->where(function ($q) use ($code, $dto) {
                $q->where('codigo_inventario', $code);
                if (! empty($dto->serialNumber)) {
                    $q->orWhere('numero_serie', $dto->serialNumber);
                }
            })
            ->where('estado', '!=', 'de_baja') // #3: la baja libera el código patrimonial
            ->exists();

        if ($duplicate) {
            throw new \DomainException(
                "Ya existe un equipo con el cÃ³digo '{$code}'"
                .(! empty($dto->serialNumber) ? " o el serial '{$dto->serialNumber}'" : '').'.'
            );
        }

        $id = (int) DB::table('equipos')->insertGetId([
            'codigo_inventario' => $code,
            'numero_serie' => $dto->serialNumber,
            'tipo' => $dto->type,
            'marca' => $dto->brand,
            'modelo' => $dto->model,
            'estado' => $statusEnum->value,
            'departamento_id' => $dto->departmentId,
            'empleado_id' => $dto->employeeId,
            'ubicacion_fisica' => $dto->physicalLocation,
            'procesador' => $dto->processor,
            'memoria_ram' => $dto->ram,
            'almacenamiento' => $dto->storage,
            'sistema_operativo' => $dto->os,
            'direccion_ip' => $dto->ipAddress,
            'driver' => $dto->driver,
            'toner' => $dto->toner,
            'fecha_compra' => $dto->purchaseDate,
            'proveedor' => $dto->supplier,
            'garantia' => $dto->warranty,
            'valor_compra' => $dto->purchaseValue,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Cadena custodial: si nace con custodio, arranca con eslabón formal.
        if ($dto->employeeId !== null) {
            $this->assignCustody($id, (int) $dto->employeeId, null, 'asignacion_inicial');
        }

        return $id;
    }

    public function transfer(TransferEquipmentDTO $dto): void
    {
        $updated = DB::table('equipos')
            ->where('id', $dto->equipoId)
            ->where('departamento_id', $dto->departamentoOrigenId)
            ->update([
                'departamento_id' => $dto->departamentoDestinoId,
                'updated_at' => Carbon::now(),
            ]);

        if ($updated === 0) {
            $equipo = DB::table('equipos')->where('id', $dto->equipoId)->first();

            if ($equipo === null) {
                throw EquipmentNotFoundException::withId($dto->equipoId);
            }

            throw new \DomainException(
                "El equipo #{$dto->equipoId} no pertenece al departamento de origen #{$dto->departamentoOrigenId}."
            );
        }
    }

    public function update(int $id, UpdateEquipmentDTO $dto): void
    {
        $equipoActual = DB::table('equipos')->where('id', $id)->first(['id', 'empleado_id']);
        if ($equipoActual === null) {
            throw EquipmentNotFoundException::withId($id);
        }

        $payload = ['updated_at' => Carbon::now()];

        if ($dto->inventoryCode !== null) {
            $payload['codigo_inventario'] = $dto->inventoryCode;
        }
        if ($dto->serialNumber !== null) {
            $payload['numero_serie'] = $dto->serialNumber;
        }
        if ($dto->type !== null) {
            $payload['tipo'] = $dto->type;
        }
        if ($dto->brand !== null) {
            $payload['marca'] = $dto->brand;
        }
        if ($dto->model !== null) {
            $payload['modelo'] = $dto->model;
        }
        if ($dto->status !== null) {
            $statusEnum = EquipmentStatus::tryFrom($dto->status) ?? EquipmentStatus::fromLabel($dto->status);
            $payload['estado'] = $statusEnum->value;
        }
        if ($dto->hasDepartmentId) {
            $payload['departamento_id'] = $dto->departmentId;
        } elseif ($dto->departmentId !== null) {
            $payload['departamento_id'] = $dto->departmentId;
        }
        if ($dto->hasEmployeeId) {
            $payload['empleado_id'] = $dto->employeeId;
        } elseif ($dto->employeeId !== null) {
            $payload['empleado_id'] = $dto->employeeId;
        }
        if ($dto->physicalLocation !== null) {
            $payload['ubicacion_fisica'] = $dto->physicalLocation;
        }
        if ($dto->processor !== null) {
            $payload['procesador'] = $dto->processor;
        }
        if ($dto->ram !== null) {
            $payload['memoria_ram'] = $dto->ram;
        }
        if ($dto->storage !== null) {
            $payload['almacenamiento'] = $dto->storage;
        }
        if ($dto->os !== null) {
            $payload['sistema_operativo'] = $dto->os;
        }
        if ($dto->ipAddress !== null) {
            $payload['direccion_ip'] = $dto->ipAddress;
        }
        if ($dto->driver !== null) {
            $payload['driver'] = $dto->driver;
        }
        if ($dto->toner !== null) {
            $payload['toner'] = $dto->toner;
        }
        if ($dto->purchaseDate !== null) {
            $payload['fecha_compra'] = $dto->purchaseDate;
        }
        if ($dto->supplier !== null) {
            $payload['proveedor'] = $dto->supplier;
        }
        if ($dto->warranty !== null) {
            $payload['garantia'] = $dto->warranty;
        }
        if ($dto->purchaseValue !== null) {
            $payload['valor_compra'] = $dto->purchaseValue;
        }

        // #22 locking optimista: si el lector trajo versión, el UPDATE solo
        // aplica si la fila sigue intacta desde entonces (y siempre sube).
        $query = DB::table('equipos')->where('id', $id);
        if ($dto->version !== null) {
            $query->where('version', $dto->version);
        }
        $payload['version'] = DB::raw('version + 1');
        $actualizadas = $query->update($payload);

        if ($actualizadas === 0 && $dto->version !== null) {
            throw \App\Exceptions\OptimisticLockException::forEntity('Equipo', $id);
        }

        // Cadena custodial: solo se rotulan eslabones cuando el custodio
        // cambia de verdad (nunca dos activas; cierre y apertura atómicos).
        if ($dto->hasEmployeeId) {
            $nuevo = $dto->employeeId !== null ? (int) $dto->employeeId : null;
            $actual = $equipoActual->empleado_id !== null ? (int) $equipoActual->empleado_id : null;

            if ($nuevo !== $actual) {
                $this->assignCustody($id, $nuevo, auth()->id() !== null ? (int) auth()->id() : null, 'reasignacion');
            }
        }
    }

    public function delete(int $id): void
    {
        // La cadena custodial convierte al equipo en historia patrimonial
        // inmutable: borrado físico prohibido; la baja formal es su vía.
        $custodias = (int) DB::table('custodias')->where('equipo_id', $id)->count();
        if ($custodias > 0) {
            throw new \DomainException(
                "No se puede eliminar el equipo: tiene {$custodias} eslabón(es) de cadena custodial. "
                .'Registre su baja formal en su lugar (conserva la trazabilidad patrimonial).'
            );
        }

        // Ni un solo ticket ni una orden de mantenimiento deben desaparecer
        // en cascada patrimonial: si existe historia operativa, el activo
        // se preserva. Su baja pasa por el wizard formal (módulo 11).
        $tickets = (int) DB::table('soportes')->where('equipo_id', $id)->count();
        $mantenimientos = (int) DB::table('mantenimientos')->where('equipo_id', $id)->count();
        if ($tickets > 0 || $mantenimientos > 0) {
            $detalle = trim("{$tickets} ticket(s), {$mantenimientos} mantenimiento(s)", ', ');
            throw new \DomainException(
                "No se puede eliminar el equipo: conserva historia operativa ({$detalle}). "
                .'La vía correcta es la baja formal con acta, no el borrado físico.'
            );
        }

        // Retiro a papelera (restaurable), no destrucción: la evidencia se
        // conserva y la ficha desaparece de la operación diaria.
        $deleted = DB::table('equipos')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => Carbon::now(), 'updated_at' => Carbon::now()]);

        if ($deleted === 0) {
            throw EquipmentNotFoundException::withId($id);
        }
    }

    // ── Cadena custodial (Módulo 12) ────────────────────────────────────

    public function assignCustody(int $equipoId, ?int $empleadoId, ?int $actorUserId, ?string $motivo = null): void
    {
        DB::transaction(function () use ($equipoId, $empleadoId, $actorUserId, $motivo): void {
            // Lock del equipo: dos reasignaciones concurrentes no pueden
            // dejar dos custodias activas ni perder el eslabón intermedio.
            $equipo = DB::table('equipos')->where('id', $equipoId)->lockForUpdate()
                ->first(['id', 'empleado_id', 'estado']);

            if ($equipo === null) {
                throw EquipmentNotFoundException::withId($equipoId);
            }

            if ($equipo->estado === 'de_baja') {
                throw new \DomainException('No se puede asignar custodia: el equipo está de baja; su historia se conserva, pero su ciclo patrimonial terminó.');
            }

            $now = Carbon::now();

            // Cierra la custodia vigente (si la hay).
            DB::table('custodias')
                ->where('equipo_id', $equipoId)
                ->whereNull('fecha_fin')
                ->update(['fecha_fin' => $now, 'updated_at' => $now]);

            // Abre la nueva custodia solo si hay custodio destino.
            if ($empleadoId !== null) {
                DB::table('custodias')->insert([
                    'equipo_id' => $equipoId,
                    'empleado_id' => $empleadoId,
                    'asignado_por' => $actorUserId,
                    'fecha_inicio' => $now,
                    'fecha_fin' => null,
                    'motivo' => $motivo ?? ($equipo->empleado_id !== null ? 'reasignacion' : 'asignacion_inicial'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    public function signCustody(int $equipoId, string $signatureData, string $ipAddress, ?string $userAgent): void
    {
        DB::transaction(function () use ($equipoId, $signatureData, $ipAddress, $userAgent): void {
            $custodia = DB::table('custodias')
                ->where('equipo_id', $equipoId)
                ->whereNull('fecha_fin')
                ->lockForUpdate()
                ->first(['id', 'firma']);

            if ($custodia === null) {
                throw new \DomainException('El equipo no tiene una custodia vigente: asigne primero un custodio.');
            }

            // Firma única e inmutable (mismo discurso probatorio que tickets).
            if ($custodia->firma !== null) {
                throw new \DomainException('La custodia vigente ya está firmada; su evidencia probatoria es inmutable.');
            }

            DB::table('custodias')->where('id', $custodia->id)->update([
                'firma' => $signatureData,
                'firma_hash_sha256' => hash('sha256', $signatureData),
                'firma_ip' => mb_substr($ipAddress, 0, 45),
                'firma_user_agent' => $userAgent !== null ? mb_substr($userAgent, 0, 512) : null,
                'firmado_en' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        });
    }

    public function currentCustody(int $equipoId): ?array
    {
        $row = DB::table('custodias')
            ->leftJoin('empleados', 'custodias.empleado_id', '=', 'empleados.id')
            ->leftJoin('usuarios', 'custodias.asignado_por', '=', 'usuarios.id')
            ->where('custodias.equipo_id', $equipoId)
            ->whereNull('custodias.fecha_fin')
            ->select([
                'custodias.id', 'custodias.empleado_id', 'custodias.motivo',
                'custodias.fecha_inicio', 'custodias.firmado_en',
                'custodias.firma_hash_sha256', 'custodias.firma_ip',
                'empleados.nombre as empleado_nombre', 'empleados.apellido as empleado_apellido',
                'usuarios.username as asignado_por_username',
            ])
            ->first();

        return $row !== null ? $this->mapCustodyRow($row) : null;
    }

    public function custodyHistory(int $equipoId): array
    {
        return DB::table('custodias')
            ->leftJoin('empleados', 'custodias.empleado_id', '=', 'empleados.id')
            ->leftJoin('usuarios', 'custodias.asignado_por', '=', 'usuarios.id')
            ->where('custodias.equipo_id', $equipoId)
            ->orderByDesc('custodias.fecha_inicio')
            ->orderByDesc('custodias.id') /* misma marca temporal: el más nuevo primero */
            ->select([
                'custodias.id', 'custodias.empleado_id', 'custodias.motivo',
                'custodias.fecha_inicio', 'custodias.fecha_fin', 'custodias.firmado_en',
                'custodias.firma_hash_sha256', 'custodias.firma_ip',
                'empleados.nombre as empleado_nombre', 'empleados.apellido as empleado_apellido',
                'usuarios.username as asignado_por_username',
            ])
            ->get()
            ->map(fn ($row) => $this->mapCustodyRow($row))
            ->all();
    }

    public function activeCustodyIdsOfEmployee(int $empleadoId): array
    {
        return DB::table('custodias')
            ->where('empleado_id', $empleadoId)
            ->whereNull('fecha_fin')
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCustodyRow(object $row): array
    {
        $custodio = trim(($row->empleado_nombre ?? '').' '.($row->empleado_apellido ?? ''));

        return [
            'id' => (int) $row->id,
            'empleado_id' => (int) $row->empleado_id,
            'custodio' => $custodio !== '' ? $custodio : 'Sin nombre',
            'motivo' => (string) ($row->motivo ?? 'asignacion'),
            'fecha_inicio' => isset($row->fecha_inicio) ? Carbon::parse((string) $row->fecha_inicio)->format('d/m/Y H:i') : null,
            'fecha_fin' => isset($row->fecha_fin) ? Carbon::parse((string) $row->fecha_fin)->format('d/m/Y H:i') : null,
            'vigente' => ! isset($row->fecha_fin),
            'firmada' => isset($row->firmado_en),
            'firmado_en' => isset($row->firmado_en) ? Carbon::parse((string) $row->firmado_en)->format('d/m/Y H:i') : null,
            'firma_hash' => isset($row->firma_hash_sha256) ? (string) $row->firma_hash_sha256 : null,
            'firma_ip' => isset($row->firma_ip) ? (string) $row->firma_ip : null,
            'asignado_por' => isset($row->asignado_por_username) ? (string) $row->asignado_por_username : 'Sistema',
        ];
    }

    public function getFormOptions(): array
    {
        $departments = DB::table('departamentos')
            ->select(['id', 'nombre'])
            ->orderBy('nombre')
            ->get()
            ->map(fn ($d) => ['id' => (int) $d->id, 'nombre' => (string) $d->nombre])
            ->all();

        $employees = DB::table('empleados')
            ->select(['id', 'nombre', 'apellido', 'cargo'])
            ->orderBy('nombre')
            ->get()
            ->map(fn ($e) => [
                'id' => (int) $e->id,
                'nombre' => (string) $e->nombre,
                'apellido' => (string) ($e->apellido ?? ''),
                'nombre_completo' => trim($e->nombre.' '.($e->apellido ?? '')),
                'cargo' => $e->cargo,
            ])
            ->all();

        return [
            'departments' => $departments,
            'employees' => $employees,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Modules\Support\Infrastructure\Persistence\Eloquent;

use App\Exceptions\OptimisticLockException;
use App\Support\Config\ConfiguracionGlobal;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Support\Application\DTOs\CreateTicketDTO;
use Modules\Support\Application\DTOs\SupportKpisDTO;
use Modules\Support\Application\DTOs\TicketDetailDTO;
use Modules\Support\Application\DTOs\UpdateTicketDTO;
use Modules\Support\Application\Mappers\TicketDetailMapper;
use Modules\Support\Application\Mappers\TicketListItemMapper;
use Modules\Support\Domain\Enums\TicketStatus;
use Modules\Support\Domain\Exceptions\InvalidTicketStatusTransitionException;
use Modules\Support\Domain\Exceptions\RatingNotAllowedException;
use Modules\Support\Domain\Exceptions\ReopenNotAllowedException;
use Modules\Support\Domain\Exceptions\SignatureAlreadyRegisteredException;
use Modules\Support\Domain\Exceptions\TicketNotFoundException;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;
use Modules\Support\Domain\Services\SlaPolicy;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class EloquentSupportRepository implements SupportRepositoryInterface
{
    public function getKpis(?int $currentUserId = null): SupportKpisDTO
    {
        $criticalPending = (int) DB::table('soportes')->whereNull('deleted_at')
            ->where('prioridad', 'critica')
            ->whereNotIn('estado', TicketStatus::finalValues())
            ->count();

        $generalQueue = (int) DB::table('soportes')->whereNull('deleted_at')
            ->where('estado', 'pendiente')
            ->count();

        $inProcess = (int) DB::table('soportes')->whereNull('deleted_at')
            ->where('estado', 'en_proceso')
            ->count();

        // "Resueltos" = resuelto + cerrado (autocierre). Un ticket cancelado
        // nunca fue resuelto y no debe inflar el KPI.
        $resolvedTickets = (int) DB::table('soportes')->whereNull('deleted_at')
            ->whereIn('estado', [TicketStatus::RESUELTO->value, TicketStatus::CERRADO->value])
            ->count();

        $totalTickets = (int) DB::table('soportes')->whereNull('deleted_at')->count();

        // Si hay usuario logueado, buscar su empleado_id
        $myAssignments = 0;
        if ($currentUserId !== null) {
            $empId = DB::table('usuarios')->where('id', $currentUserId)->value('empleado_id');
            if ($empId) {
                $myAssignments = (int) DB::table('soportes')
                    ->whereNull('deleted_at')
                    ->where('empleado_id', $empId)
                    ->whereNotIn('estado', TicketStatus::finalValues())
                    ->count();
            }
        }

        return new SupportKpisDTO(
            criticalPending: $criticalPending,
            generalQueue: $generalQueue,
            inProcess: $inProcess,
            myAssignments: $myAssignments,
            totalTickets: $totalTickets,
            resolvedTickets: $resolvedTickets
        );
    }

    public function listTickets(array $filters = []): array
    {
        $currentUserId = isset($filters['user_id']) ? (int) $filters['user_id'] : null;
        // Identidad del empleado vinculado al usuario actual (para "mis asignaciones").
        $miEmpleadoId = $currentUserId !== null
            ? (int) (DB::table('usuarios')->where('id', $currentUserId)->value('empleado_id') ?? 0)
            : null;
        if ($miEmpleadoId === 0) {
            $miEmpleadoId = null;
        }

        // La papelera oficial-retira los tickets; Contadores excluyéndolos simultáneamente.
        $query = DB::table('soportes')
            ->whereNull('soportes.deleted_at')
            ->leftJoin('categorias', 'soportes.categoria_id', '=', 'categorias.id')
            ->leftJoin('empleados as tech', 'soportes.empleado_id', '=', 'tech.id')
            ->leftJoin('equipos', 'soportes.equipo_id', '=', 'equipos.id')
            ->leftJoin('empleados as requester', 'equipos.empleado_id', '=', 'requester.id')
            ->leftJoin('departamentos', 'equipos.departamento_id', '=', 'departamentos.id')
            ->leftJoin('departamentos as tech_depto', 'tech.departamento_id', '=', 'tech_depto.id')
            ->select([
                'soportes.id',
                'soportes.codigo',
                'soportes.titulo',
                'soportes.descripcion',
                'soportes.estado',
                'soportes.prioridad',
                'soportes.fecha',
                'soportes.usuario_creacion_id',
                'soportes.empleado_id',
                'categorias.nombre as categoria_nombre',
                'tech.id as tech_id',
                'tech.nombre as tech_nombre',
                'tech.apellido as tech_apellido',
                'tech_depto.nombre as tech_depto_nombre',
                'requester.nombre as req_nombre',
                'requester.apellido as req_apellido',
                'departamentos.nombre as depto_nombre',
                'equipos.numero_serie as serial_equipo',
            ])
            ->orderByDesc('soportes.id');

        // Filtro por estado
        if (! empty($filters['estado']) && $filters['estado'] !== 'todos' && $filters['estado'] !== 'all') {
            $estado = strtolower(trim((string) $filters['estado']));
            if ($estado === 'proceso' || $estado === 'en_proceso' || $estado === 'process') {
                $query->where('soportes.estado', 'en_proceso');
            } elseif ($estado === 'pendiente' || $estado === 'pending') {
                $query->where('soportes.estado', 'pendiente');
            } elseif ($estado === 'resuelto' || $estado === 'resolved') {
                $query->where('soportes.estado', 'resuelto');
            } elseif ($estado === 'cerrado' || $estado === 'closed') {
                $query->where('soportes.estado', 'cerrado');
            } elseif ($estado === 'en_espera' || $estado === 'espera' || $estado === 'waiting') {
                $query->where('soportes.estado', 'en_espera');
            } elseif ($estado === 'critica' || $estado === 'critical') {
                $query->where(function ($q) {
                    $q->where('soportes.prioridad', 'critica')
                        ->orWhere('soportes.estado', 'pendiente');
                });
            } else {
                $query->where('soportes.estado', $estado);
            }
        }

        // Alcance por fila: el solicitante (rol operador) solo ve lo suyo.
        if (! empty($filters['solo_propios']) && ! empty($filters['user_id'])) {
            $query->where('soportes.usuario_creacion_id', (int) $filters['user_id']);
        }

        // Búsqueda de texto
        if (! empty($filters['search'])) {
            $search = '%'.trim((string) $filters['search']).'%';
            $query->where(function ($q) use ($search) {
                $q->where('soportes.titulo', 'ILIKE', $search)
                    ->orWhere('soportes.descripcion', 'ILIKE', $search)
                    ->orWhere('tech.nombre', 'ILIKE', $search)
                    ->orWhere('tech.apellido', 'ILIKE', $search)
                    ->orWhere('requester.nombre', 'ILIKE', $search)
                    ->orWhere('requester.apellido', 'ILIKE', $search)
                    ->orWhere('equipos.numero_serie', 'ILIKE', $search)
                    ->orWhere('departamentos.nombre', 'ILIKE', $search);
            });
        }

        $records = $query->limit(50)->get();

        // Conteos de comentarios SOLO para la página visible (evita escaneo total de la tabla).
        $pageIds = $records->pluck('id')->all();
        $commentCounts = $pageIds === []
            ? []
            : DB::table('ticket_comentarios')
                ->select('ticket_id', DB::raw('count(*) as count'))
                ->whereIn('ticket_id', $pageIds)
                ->groupBy('ticket_id')
                ->pluck('count', 'ticket_id')
                ->all();

        return $records
            ->map(fn ($row) => TicketListItemMapper::fromDatabaseRow($row, $commentCounts, $currentUserId, $miEmpleadoId))
            ->all();
    }

    public function findById(int $id, ?int $viewerUserId = null): ?TicketDetailDTO
    {
        $ticket = DB::table('soportes')
            ->leftJoin('categorias', 'soportes.categoria_id', '=', 'categorias.id')
            ->leftJoin('empleados as tech', 'soportes.empleado_id', '=', 'tech.id')
            ->leftJoin('usuarios as creator', 'soportes.usuario_creacion_id', '=', 'creator.id')
            ->leftJoin('equipos', 'soportes.equipo_id', '=', 'equipos.id')
            ->leftJoin('empleados as requester', 'equipos.empleado_id', '=', 'requester.id')
            ->leftJoin('departamentos', 'equipos.departamento_id', '=', 'departamentos.id')
            ->where('soportes.id', $id)
            ->select([
                'soportes.*',
                'categorias.nombre as categoria_nombre',
                'tech.id as tech_id',
                'tech.nombre as tech_nombre',
                'tech.apellido as tech_apellido',
                'creator.username as creator_username',
                'requester.id as req_id',
                'requester.nombre as req_nombre',
                'requester.apellido as req_apellido',
                'departamentos.id as depto_id',
                'departamentos.nombre as depto_nombre',
                'equipos.id as eq_id',
                'equipos.numero_serie as eq_serial',
                'equipos.tipo as eq_tipo',
                'equipos.modelo as eq_modelo',
            ])
            ->first();

        if (! $ticket) {
            return null;
        }

        // Comentarios: las notas internas solo son visibles para personal
        // operativo. El solicitante (operador) no debe verlas en el detalle.
        if ($viewerUserId !== null) {
            $viewerRole = (string) (DB::table('usuarios')->where('id', $viewerUserId)->value('rol') ?? '');
        } else {
            $viewer = auth()->user();
            $viewerRole = $viewer !== null ? (string) ($viewer->rol ?? '') : '';
        }
        $canSeeInternal = in_array($viewerRole, ['admin', 'tecnico', 'consultor'], true);

        $commentsQuery = DB::table('ticket_comentarios')
            ->leftJoin('usuarios', 'ticket_comentarios.usuario_id', '=', 'usuarios.id')
            ->where('ticket_id', $id);

        if (! $canSeeInternal) {
            $commentsQuery->where(function ($q): void {
                $q->where('ticket_comentarios.es_interno', false)
                    ->orWhereNull('ticket_comentarios.es_interno');
            });
        }

        $comments = $commentsQuery
            ->select([
                'ticket_comentarios.id',
                'ticket_comentarios.comentario',
                'ticket_comentarios.es_interno',
                'ticket_comentarios.fecha',
                'usuarios.username',
            ])
            ->orderBy('fecha')
            ->get();

        // Archivos Adjuntos
        $attachments = DB::table('ticket_archivos')
            ->where('ticket_id', $id)
            ->get();

        // Materiales Consumidos
        $materials = DB::table('inventario_consumos')
            ->leftJoin('inventario_items', 'inventario_consumos.item_id', '=', 'inventario_items.id')
            ->where('soporte_id', $id)
            ->select([
                'inventario_consumos.id',
                'inventario_consumos.cantidad',
                'inventario_consumos.fecha',
                'inventario_items.nombre as item_nombre',
                'inventario_items.codigo as item_codigo',
            ])
            ->get();

        return TicketDetailMapper::toDTO($ticket, $comments, $attachments, $materials);
    }

    public function createTicket(CreateTicketDTO $dto, ?int $userId = null, ?string $fechaVencimiento = null): int
    {
        $now = Carbon::now();

        // Numeración legible transaccional (TIC-AAAA-#####): el correlativo se
        // consume dentro de la misma transacción + lock de fila — imposible
        // duplicar el código aunque dos altas compitan en el mismo instante.
        return (int) DB::transaction(function () use ($dto, $userId, $fechaVencimiento, $now) {
            $codigo = self::nextCorrelativoTicket($now);

            // Regla #30: un equipo dado de baja no puede recibir tickets nuevos;
            // su historia se conserva, pero su ciclo operativo ya terminó.
            $estadoEquipo = DB::table('equipos')->where('id', (int) $dto->equipoId)->value('estado');
            if ($estadoEquipo !== null && (string) $estadoEquipo === 'de_baja') {
                throw new \DomainException('No se puede registrar el ticket: el equipo seleccionado está de baja. El historial se conserva, pero ya no tiene ciclo operativo.');
            }

            return DB::table('soportes')->insertGetId([
                'codigo' => $codigo,
                'titulo' => $dto->titulo,
                'descripcion' => $dto->descripcion,
                // Canal público: link de seguimiento aleatorio desde la creación.
                'token_publico' => Str::random(40),
                'equipo_id' => $dto->equipoId,
                'categoria_id' => $dto->categoriaId,
                'empleado_id' => $dto->empleadoId,
                'usuario_creacion_id' => $userId,
                'prioridad' => $dto->prioridad,
                'estado' => $dto->estado,
                'fecha' => $now,
                'fecha_vencimiento' => $fechaVencimiento,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    /**
     * Próximo correlativo anual de tickets bajo lock transaccional.
     * Llamar SIEMPRE dentro de DB::transaction.
     */
    private static function nextCorrelativoTicket(Carbon $now): string
    {
        $clave = 'ticket:'.$now->format('Y');

        DB::table('correlativos')->insertOrIgnore([
            'clave' => $clave,
            'valor' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $actual = (int) DB::table('correlativos')
            ->where('clave', $clave)
            ->lockForUpdate()
            ->value('valor');

        $siguiente = $actual + 1;

        DB::table('correlativos')
            ->where('clave', $clave)
            ->update(['valor' => $siguiente, 'updated_at' => $now]);

        return sprintf('TIC-%s-%05d', $now->format('Y'), $siguiente);
    }

    public function updateTicket(int $id, UpdateTicketDTO $dto): bool
    {
        return DB::transaction(function () use ($id, $dto): bool {
            // Lectura bloqueada: la transición, la firma y el versionado se
            // evalúan sobre el mismo estado, sin TOCTOU entre leer y escribir.
            $row = DB::table('soportes')
                ->where('id', $id)
                ->lockForUpdate()
                ->first(['id', 'estado', 'firma', 'fecha', 'fecha_asignacion', 'tiempo_pausado_minutos', 'updated_at']);

            if ($row === null) {
                throw TicketNotFoundException::withId($id);
            }

            // Un ticket firmado es evidencia probatoria: no admite cambios.
            if ($row->firma !== null) {
                throw new \DomainException('El ticket ya está firmado: no admite modificaciones.');
            }

            $payload = ['updated_at' => Carbon::now()];

            if ($dto->titulo !== null) {
                $payload['titulo'] = $dto->titulo;
            }
            if ($dto->descripcion !== null) {
                $payload['descripcion'] = $dto->descripcion;
            }
            if ($dto->prioridad !== null) {
                $payload['prioridad'] = $dto->prioridad;
            }

            if ($dto->estado !== null) {
                $current = TicketStatus::tryFromString((string) $row->estado);
                $target = TicketStatus::tryFromString((string) $dto->estado);

                if (! $current->canTransitionTo($target)) {
                    throw InvalidTicketStatusTransitionException::from($current, $target);
                }

                $payload['estado'] = $dto->estado;

                if ($target === TicketStatus::RESUELTO) {
                    // La resolución exige solución verificable: sin esto se
                    // cierra un ticket sin dejar rastro técnico.
                    $solucion = trim((string) ($dto->solucion ?? ''));
                    if (mb_strlen($solucion) < 10) {
                        throw new \DomainException('Para resolver un ticket debes registrar una solución de al menos 10 caracteres.');
                    }

                    $now = Carbon::now();
                    $payload['fecha_resolucion'] = $now;
                    $payload['fecha_cierre'] = $now;
                    $payload['solucion'] = $solucion;

                    $start = $row->fecha_asignacion ? Carbon::parse($row->fecha_asignacion) : ($row->fecha ? Carbon::parse($row->fecha) : $now);
                    $elapsed = max(1, (int) round(abs($now->diffInMinutes($start))));
                    $paused = (int) ($row->tiempo_pausado_minutos ?? 0);

                    if ((string) $row->estado === TicketStatus::EN_ESPERA->value && $row->updated_at !== null) {
                        // Resolver desde una pausa abierta: sus minutos también
                        // se descuentan del tiempo de atención.
                        $paused += max(0, (int) round(abs($now->diffInMinutes(Carbon::parse($row->updated_at)))));
                    }

                    $payload['tiempo_atencion_minutos'] = max(1, $elapsed - $paused);

                    DB::table('ticket_comentarios')->insert([
                        'ticket_id' => $id,
                        'usuario_id' => (int) auth()->id(),
                        'comentario' => "Ticket RESUELTO. Solución técnica: {$solucion}",
                        'es_interno' => false,
                        'fecha' => $now,
                    ]);
                } elseif ($target === TicketStatus::CERRADO || $target === TicketStatus::CANCELADO) {
                    // El ciclo de vida termina: cerrado por conclusión o cancelado por anulación.
                    $payload['fecha_cierre'] = Carbon::now();
                }
            }

            if ($dto->empleadoId !== null) {
                $payload['empleado_id'] = $dto->empleadoId;
            }
            if ($dto->categoriaId !== null) {
                $payload['categoria_id'] = $dto->categoriaId;
            }
            // `fecha_cierre` y `tiempo_atencion_minutos` ya no viajan en el
            // update general (el request no los acepta): solo el flujo
            // dedicado de fecha de cierre puede fijarlos.
            if ($dto->fechaCierre !== null) {
                $payload['fecha_cierre'] = $dto->fechaCierre;
            }
            if ($dto->solucion !== null && ! isset($payload['solucion'])) {
                $payload['solucion'] = $dto->solucion;
            }
            if ($dto->tiempoAtencionMinutos !== null && ! isset($payload['tiempo_atencion_minutos'])) {
                $payload['tiempo_atencion_minutos'] = $dto->tiempoAtencionMinutos;
            }
            if ($dto->firma !== null) {
                // La firma dentro de la resolución conserva la misma evidencia
                // probatoria; solo se firma al resolver o un ticket ya resuelto.
                $target = $dto->estado !== null
                    ? TicketStatus::tryFromString((string) $dto->estado)
                    : TicketStatus::tryFromString((string) $row->estado);

                if (! in_array($target, [TicketStatus::RESUELTO, TicketStatus::CERRADO], true)) {
                    throw new \DomainException('Solo se puede firmar un ticket resuelto o cerrado.');
                }

                $payload['firma'] = $dto->firma;
                $payload += self::probatorySignaturePayload($dto->firma, $dto->firmaIp ?? '0.0.0.0', $dto->firmaUserAgent);
            }

            // #22: versionado optimista del ticket. Si el editor traía una versión
            // vista y ya cambió, se rechaza con 409 y no se pisa nada en silencio.
            $payload['version'] = DB::raw('version + 1');
            $query = DB::table('soportes')->where('id', $id);
            if ($dto->version !== null) {
                $query->where('version', $dto->version);
            }

            $actualizadas = $query->update($payload);
            if ($actualizadas === 0 && $dto->version !== null) {
                // ¿Existe aún? Distingo "no hay versión" de "la fila desapareció".
                throw OptimisticLockException::forEntity('Ticket', $id);
            }

            return $actualizadas > 0;
        });
    }

    public function deleteTicket(int $id): bool
    {
        // Soft-delete a la papelera de catálogos: no destrucción del historial.
        return DB::table('soportes')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => Carbon::now(), 'updated_at' => Carbon::now()]) > 0;
    }

    public function reassignTechnician(int $id, int $employeeId): bool
    {
        // El técnico debe existir, estar vigente y ser personal operativo:
        // asignar tickets a empleados de baja o consultores corrompe la bandeja.
        $empleado = DB::table('empleados')
            ->where('id', $employeeId)
            ->whereNull('deleted_at')
            ->first(['id', 'rol']);

        if ($empleado === null) {
            throw new \DomainException("El empleado #{$employeeId} no existe o está dado de baja.");
        }

        if ((string) $empleado->rol === 'consultor') {
            throw new \DomainException('Solo personal técnico o administrador puede atender tickets.');
        }

        // Un ticket terminado no se reasigna: primero debe reabrirse.
        $ticket = DB::table('soportes')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first(['id', 'estado']);

        if ($ticket === null) {
            throw TicketNotFoundException::withId($id);
        }

        if (TicketStatus::tryFromString((string) $ticket->estado)->isFinal()) {
            throw new \DomainException('No se puede reasignar un ticket finalizado (resuelto, cerrado o cancelado).');
        }

        return DB::table('soportes')->where('id', $id)->update([
            'empleado_id' => $employeeId,
            'fecha_asignacion' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]) > 0;
    }

    public function addComment(int $ticketId, int $userId, string $comment, bool $isInternal = false): bool
    {
        return DB::table('ticket_comentarios')->insert([
            'ticket_id' => $ticketId,
            'usuario_id' => $userId,
            'comentario' => $comment,
            'es_interno' => $isInternal,
            'fecha' => Carbon::now(),
        ]);
    }

    public function addMaterial(int $ticketId, int $itemId, float $quantity, int $userId): bool
    {
        return DB::table('inventario_consumos')->insert([
            'soporte_id' => $ticketId,
            'item_id' => $itemId,
            'cantidad' => $quantity,
            'usuario_id' => $userId,
            'fecha' => Carbon::now(),
        ]);
    }

    public function rateTicket(int $ticketId, string $rating, ?string $comment, int $actingUserId): bool
    {
        return DB::transaction(function () use ($ticketId, $rating, $comment, $actingUserId): bool {
            // Lock de fila: la unicidad de la calificación no depende del
            // orden de llegada de dos clics simultáneos del solicitante.
            $row = DB::table('soportes')
                ->where('id', $ticketId)
                ->lockForUpdate()
                ->first(['id', 'usuario_creacion_id', 'valoracion']);

            if ($row === null) {
                throw TicketNotFoundException::withId($ticketId);
            }

            // Regla de aplicación #32: calificación única y solo del solicitante.
            if ($row->usuario_creacion_id !== null && (int) $row->usuario_creacion_id !== $actingUserId) {
                throw RatingNotAllowedException::notRequester($ticketId);
            }

            if ($row->valoracion !== null) {
                throw RatingNotAllowedException::alreadyRated($ticketId);
            }

            return DB::table('soportes')->where('id', $ticketId)->update([
                'valoracion' => $rating,
                'valoracion_comentario' => $comment,
                'valoracion_fecha' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]) > 0;
        });
    }

    public function pauseTicket(int $ticketId, string $motivo, Carbon $pausedAt): bool
    {
        return DB::transaction(function () use ($ticketId, $motivo, $pausedAt): bool {
            $ticket = DB::table('soportes')
                ->where('id', $ticketId)
                ->lockForUpdate()
                ->first(['id', 'estado']);

            if ($ticket === null) {
                throw TicketNotFoundException::withId($ticketId);
            }

            $current = TicketStatus::tryFromString((string) $ticket->estado);

            // Solo se pausa trabajo en curso: pausar un estado terminal lo
            // devolvería a EN_ESPERA resucitando el ciclo de vida.
            if (! in_array($current, [TicketStatus::PENDIENTE, TicketStatus::EN_PROCESO], true)) {
                throw InvalidTicketStatusTransitionException::from($current, TicketStatus::EN_ESPERA);
            }

            return DB::table('soportes')->where('id', $ticketId)->update([
                'estado' => 'en_espera',
                'motivo_pausa' => $motivo,
                'updated_at' => $pausedAt,
            ]) > 0;
        });
    }

    public function resumeTicket(int $ticketId, ?Carbon $resumedAt = null): bool
    {
        $resumedAt ??= Carbon::now();

        return DB::transaction(function () use ($ticketId, $resumedAt): bool {
            $ticket = DB::table('soportes')
                ->where('id', $ticketId)
                ->lockForUpdate()
                ->first();

            if ($ticket === null) {
                return false;
            }

            $current = TicketStatus::tryFromString((string) $ticket->estado);

            // Reanudar solo tiene sentido desde una pausa real: cualquier otro
            // estado se rechaza en vez de forzar EN_PROCESO a ciegas.
            if ($current !== TicketStatus::EN_ESPERA) {
                throw InvalidTicketStatusTransitionException::from($current, TicketStatus::EN_PROCESO);
            }

            $lastUpdated = $ticket->updated_at ? Carbon::parse($ticket->updated_at) : $resumedAt;
            $elapsedMinutes = max(0, (int) round(abs($resumedAt->diffInMinutes($lastUpdated))));

            $payload = [
                'estado' => 'en_proceso',
                'motivo_pausa' => null,
                'tiempo_pausado_minutos' => (int) ($ticket->tiempo_pausado_minutos ?? 0) + $elapsedMinutes,
                'updated_at' => $resumedAt,
            ];

            if ($ticket->fecha_vencimiento !== null) {
                // Extensión de fecha de vencimiento en TIEMPO LABORAL real:
                // pausar sobre domingo/feriado no rellena horas inútiles.
                $payload['fecha_vencimiento'] = SlaPolicy::fromConfig()
                    ->withFeriados(self::feriadosProximos())
                    ->extendDueDateBusiness(
                        CarbonImmutable::parse((string) $ticket->fecha_vencimiento),
                        $elapsedMinutes
                    )
                    ->toDateTimeString();
            }

            return DB::table('soportes')->where('id', $ticketId)->update($payload) > 0;
        });
    }

    public function updateCloseDate(int $ticketId, string $newDate): bool
    {
        return DB::table('soportes')->where('id', $ticketId)->update([
            'fecha_cierre' => Carbon::parse($newDate),
            'updated_at' => Carbon::now(),
        ]) > 0;
    }

    public function bulkDeleteTickets(array $ticketIds): int
    {
        // Soft-delete (a la papelera de catálogos): el historial de tickets
        // se retira de las bandejas activas, jamás desaparece en cascada.
        return DB::table('soportes')
            ->whereIn('id', $ticketIds)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
    }

    public function saveSignature(int $ticketId, string $signatureData, string $ipAddress, ?string $userAgent, ?int $actingUserId = null): bool
    {
        return DB::transaction(function () use ($ticketId, $signatureData, $ipAddress, $userAgent, $actingUserId): bool {
            // Lock de fila: dos envíos simultáneos no pueden reescribir la
            // conformidad firmada ni dejarla a medias.
            $row = DB::table('soportes')
                ->where('id', $ticketId)
                ->lockForUpdate()
                ->first(['id', 'firma', 'estado', 'usuario_creacion_id']);

            if ($row === null) {
                throw TicketNotFoundException::withId($ticketId);
            }

            if ($row->firma !== null) {
                throw SignatureAlreadyRegisteredException::forTicket($ticketId);
            }

            // La conformidad se firma sobre un ciclo cerrado, no sobre un
            // ticket en curso (evita firmar preventivamente y bloquear cambios).
            $estado = TicketStatus::tryFromString((string) $row->estado);
            if (! in_array($estado, [TicketStatus::RESUELTO, TicketStatus::CERRADO], true)) {
                throw new \DomainException("Solo se puede firmar un ticket resuelto o cerrado (estado actual: {$estado->label()}).");
            }

            // Firmante legítimo: el solicitante o personal operativo actuando
            // en su nombre. Nunca un tercero ajeno al ticket.
            if ($actingUserId !== null && $actingUserId > 0) {
                $rol = DB::table('usuarios')->where('id', $actingUserId)->value('rol');
                $esPersonal = in_array((string) $rol, ['admin', 'tecnico'], true);
                $esSolicitante = $row->usuario_creacion_id !== null
                    && (int) $row->usuario_creacion_id === $actingUserId;

                if (! $esPersonal && ! $esSolicitante) {
                    throw new AccessDeniedHttpException("Solo el solicitante o el personal operativo puede firmar el ticket #{$ticketId}.");
                }
            }

            return DB::table('soportes')->where('id', $ticketId)->update([
                'firma' => $signatureData,
                ...self::probatorySignaturePayload($signatureData, $ipAddress, $userAgent),
                'updated_at' => Carbon::now(),
            ]) > 0;
        });
    }

    /**
     * Evidencia probatoria de una firma: hash del contenido firmado, red y
     * agente del firmante, y sello de tiempo. Compartida por los dos flujos
     * que capturan firma (ruta dedicada y resolución con conformidad).
     *
     * @return array{firma_hash_sha256: string, firma_ip: string, firma_user_agent: ?string, firmado_en: Carbon}
     */
    private static function probatorySignaturePayload(string $signatureData, string $ipAddress, ?string $userAgent): array
    {
        return [
            'firma_hash_sha256' => hash('sha256', $signatureData),
            'firma_ip' => mb_substr($ipAddress, 0, 45),
            'firma_user_agent' => $userAgent !== null ? mb_substr($userAgent, 0, 512) : null,
            'firmado_en' => Carbon::now(),
        ];
    }

    public function uploadAttachment(int $ticketId, string $filePath, string $originalName, string $mimeType, int $size, int $userId, ?string $checksumSha256 = null): int
    {
        return (int) DB::table('ticket_archivos')->insertGetId([
            'ticket_id' => $ticketId,
            'nombre_archivo' => basename($filePath),
            'nombre_original' => $originalName,
            'ruta' => $filePath,
            'tipo_mime' => $mimeType,
            'tamano_bytes' => $size,
            'checksum_sha256' => $checksumSha256,
            'subido_por' => $userId,
            'fecha_subida' => Carbon::now(),
        ]);
    }

    public function getAttachmentById(int $attachmentId): ?object
    {
        return DB::table('ticket_archivos')->where('id', $attachmentId)->first();
    }

    public function deleteAttachment(int $attachmentId): bool
    {
        return DB::table('ticket_archivos')->where('id', $attachmentId)->delete() > 0;
    }

    public function reopenTicket(int $ticketId, string $motivo, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($ticketId, $motivo, $userId): bool {
            $ticket = DB::table('soportes')
                ->where('id', $ticketId)
                ->lockForUpdate()
                ->first(['id', 'estado', 'fecha_resolucion', 'usuario_creacion_id']);

            if ($ticket === null) {
                throw TicketNotFoundException::withId($ticketId);
            }

            // Regla #31 · actor legítimo: el solicitante, o personal operativo
            // (técnico/administrador) actuando en su nombre. La autorización
            // se valida PRIMERO para no exponer transiciones de estado a un intruso.
            $actingUserId = (int) ($userId ?? auth()->id());
            if ($actingUserId > 0) {
                $rol = DB::table('usuarios')->where('id', $actingUserId)->value('rol');
                $esPersonal = in_array((string) $rol, ['admin', 'tecnico'], true);
                $esSolicitante = $ticket->usuario_creacion_id !== null
                    && (int) $ticket->usuario_creacion_id === $actingUserId;

                if (! $esPersonal && ! $esSolicitante) {
                    // No es un fallo de negocio: es autorización (403).
                    throw new AccessDeniedHttpException("Solo el solicitante o el personal operativo puede reabrir el ticket #{$ticketId}.");
                }
            }

            // La legalidad de la transición la dicta la máquina de estados
            // del dominio: un ticket CERRADO jamás puede reabrirse. Además la
            // reapertura solo aplica a un ticket que estuvo resuelto: usarla
            // sobre pendiente/en_proceso saltaría el flujo normal.
            $current = TicketStatus::tryFromString((string) $ticket->estado);

            if ($current !== TicketStatus::RESUELTO) {
                throw InvalidTicketStatusTransitionException::from($current, TicketStatus::EN_PROCESO);
            }

            // Regla #31 · ventana temporal desde la resolución real.
            if ($ticket->fecha_resolucion !== null) {
                $dias = max(1, ConfiguracionGlobal::entero('tickets.ventana_reapertura_dias', 7));
                $limite = Carbon::parse($ticket->fecha_resolucion)->addDays($dias);

                if (Carbon::now()->greaterThan($limite)) {
                    throw ReopenNotAllowedException::expiredWindow($ticketId, $dias);
                }
            }

            $updated = DB::table('soportes')->where('id', $ticketId)->update([
                'estado' => TicketStatus::EN_PROCESO->value,
                'fecha_cierre' => null,
                // Sin esto la ventana de reapertura seguía corriendo desde la
                // resolución vieja y el segundo cierre quedaba mal fechado.
                'fecha_resolucion' => null,
                'motivo_pausa' => null,
                'updated_at' => Carbon::now(),
            ]) > 0;

            if ($updated) {
                DB::table('ticket_comentarios')->insert([
                    'ticket_id' => $ticketId,
                    'usuario_id' => (int) ($userId ?? auth()->id()),
                    'comentario' => "Ticket REABIERTO. Motivo: {$motivo}",
                    'es_interno' => false,
                    'fecha' => Carbon::now(),
                ]);
            }

            return $updated;
        });
    }

    public function getFormOptions(): array
    {
        $technicians = DB::table('empleados')
            ->select('id', 'nombre', 'apellido', 'email')
            ->where('rol', 'tecnico')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'name' => trim($e->nombre.' '.($e->apellido ?? '')),
                'email' => $e->email ?? '',
                'initial' => strtoupper(substr($e->nombre, 0, 1)),
                'specialty' => 'Soporte Técnico',
                'active_tickets' => (int) DB::table('soportes')->where('empleado_id', $e->id)->whereNotIn('estado', TicketStatus::finalValues())->count(),
            ])
            ->all();

        $equipments = DB::table('equipos')
            ->leftJoin('departamentos', 'equipos.departamento_id', '=', 'departamentos.id')
            ->leftJoin('empleados', 'equipos.empleado_id', '=', 'empleados.id')
            ->select([
                'equipos.id',
                'equipos.codigo_inventario as code',
                'equipos.numero_serie as serial',
                'equipos.tipo as type',
                'equipos.modelo as model',
                'departamentos.nombre as department',
                'empleados.nombre as assigned_nombre',
                'empleados.apellido as assigned_apellido',
            ])
            ->limit(100)
            ->get()
            ->map(fn ($eq) => [
                'id' => $eq->id,
                'code' => $eq->code,
                'serial' => $eq->serial,
                'type' => ucfirst($eq->type),
                'model' => $eq->model ?? 'Genérico',
                'department' => $eq->department ?? 'Sin departamento',
                'assigned_to' => trim(($eq->assigned_nombre ?? '').' '.($eq->assigned_apellido ?? '')) ?: 'Sin asignar',
            ])
            ->all();

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

        $inventoryItems = DB::table('inventario_items')
            ->select('id', 'codigo as code', 'nombre as name', 'stock_actual as stock')
            ->limit(50)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'stock' => $item->stock,
            ])
            ->all();

        return [
            'technicians' => $technicians,
            'equipments' => $equipments,
            'categories' => $categories,
            'departments' => $departments,
            'inventory_items' => $inventoryItems,
        ];
    }

    /**
     * Fechas feriadas relevantes de la ventana temporal (UX elapsed) — fuente de verdad: tabla feriados.
     *
     * @return list<string>
     */
    private static function feriadosProximos(): array
    {
        return DB::table('feriados')
            ->whereBetween('fecha', [Carbon::now()->subDay(), Carbon::now()->addMonths(12)])
            ->pluck('fecha')
            ->map(static fn ($fecha): string => CarbonImmutable::parse((string) $fecha)->format('Y-m-d'))
            ->all();
    }
}

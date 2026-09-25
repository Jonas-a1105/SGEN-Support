<?php

declare(strict_types=1);

namespace Modules\Support\Infrastructure\Persistence\Eloquent;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Support\Application\DTOs\CreateTicketDTO;
use Modules\Support\Application\DTOs\SupportKpisDTO;
use Modules\Support\Application\DTOs\TicketDetailDTO;
use Modules\Support\Application\DTOs\UpdateTicketDTO;
use Modules\Support\Application\Mappers\TicketDetailMapper;
use Modules\Support\Application\Mappers\TicketListItemMapper;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class EloquentSupportRepository implements SupportRepositoryInterface
{
    public function getKpis(?int $currentUserId = null): SupportKpisDTO
    {
        $criticalPending = (int) DB::table('soportes')
            ->where('prioridad', 'critica')
            ->where('estado', '!=', 'resuelto')
            ->count();

        $generalQueue = (int) DB::table('soportes')
            ->where('estado', 'pendiente')
            ->count();

        $inProcess = (int) DB::table('soportes')
            ->where('estado', 'en_proceso')
            ->count();

        $resolvedTickets = (int) DB::table('soportes')
            ->where('estado', 'resuelto')
            ->count();

        $totalTickets = (int) DB::table('soportes')->count();

        // Si hay usuario logueado, buscar su empleado_id
        $myAssignments = 0;
        if ($currentUserId !== null) {
            $empId = DB::table('usuarios')->where('id', $currentUserId)->value('empleado_id');
            if ($empId) {
                $myAssignments = (int) DB::table('soportes')
                    ->where('empleado_id', $empId)
                    ->where('estado', '!=', 'resuelto')
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

        $query = DB::table('soportes')
            ->leftJoin('categorias', 'soportes.categoria_id', '=', 'categorias.id')
            ->leftJoin('empleados as tech', 'soportes.empleado_id', '=', 'tech.id')
            ->leftJoin('equipos', 'soportes.equipo_id', '=', 'equipos.id')
            ->leftJoin('empleados as requester', 'equipos.empleado_id', '=', 'requester.id')
            ->leftJoin('departamentos', 'equipos.departamento_id', '=', 'departamentos.id')
            ->select([
                'soportes.id',
                'soportes.titulo',
                'soportes.descripcion',
                'soportes.estado',
                'soportes.prioridad',
                'soportes.fecha',
                'soportes.usuario_creacion_id',
                'categorias.nombre as categoria_nombre',
                'tech.id as tech_id',
                'tech.nombre as tech_nombre',
                'tech.apellido as tech_apellido',
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

        // Búsqueda de texto
        if (! empty($filters['search'])) {
            $search = '%' . trim((string) $filters['search']) . '%';
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

        // Obtener conteos de comentarios agrupados
        $commentCounts = DB::table('ticket_comentarios')
            ->select('ticket_id', DB::raw('count(*) as count'))
            ->groupBy('ticket_id')
            ->pluck('count', 'ticket_id')
            ->all();

        return $records
            ->map(fn ($row) => TicketListItemMapper::fromDatabaseRow($row, $commentCounts, $currentUserId))
            ->all();
    }

    public function findById(int $id): ?TicketDetailDTO
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

        // Comentarios
        $comments = DB::table('ticket_comentarios')
            ->leftJoin('usuarios', 'ticket_comentarios.usuario_id', '=', 'usuarios.id')
            ->where('ticket_id', $id)
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

        $id = DB::table('soportes')->insertGetId([
            'titulo' => $dto->titulo,
            'descripcion' => $dto->descripcion,
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

        return (int) $id;
    }

    public function updateTicket(int $id, UpdateTicketDTO $dto): bool
    {
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
            $payload['estado'] = $dto->estado;
            if ($dto->estado === 'resuelto') {
                $now = Carbon::now();
                $payload['fecha_resolucion'] = $now;
                $payload['fecha_cierre'] = $now;

                $ticket = DB::table('soportes')->where('id', $id)->first();
                if ($ticket) {
                    $start = $ticket->fecha_asignacion ? Carbon::parse($ticket->fecha_asignacion) : ($ticket->fecha ? Carbon::parse($ticket->fecha) : $now);
                    $elapsed = max(1, (int) round(abs($now->diffInMinutes($start))));
                    $paused = (int) ($ticket->tiempo_pausado_minutos ?? 0);
                    $payload['tiempo_atencion_minutos'] = max(1, $elapsed - $paused);
                }

                if (!empty($dto->solucion)) {
                    $payload['solucion'] = $dto->solucion;
                    $actingUserId = auth()->id() ?? (int) (DB::table('usuarios')->value('id') ?? 1);
                    DB::table('ticket_comentarios')->insert([
                        'ticket_id' => $id,
                        'usuario_id' => $actingUserId,
                        'comentario' => "Ticket RESUELTO. Solución técnica: {$dto->solucion}",
                        'es_interno' => false,
                        'fecha' => $now,
                    ]);
                }
            } elseif ($dto->estado === 'cerrado') {
                $payload['fecha_cierre'] = Carbon::now();
            }
        }
        if ($dto->empleadoId !== null) {
            $payload['empleado_id'] = $dto->empleadoId;
        }
        if ($dto->categoriaId !== null) {
            $payload['categoria_id'] = $dto->categoriaId;
        }
        if ($dto->fechaCierre !== null) {
            $payload['fecha_cierre'] = $dto->fechaCierre;
        }
        if ($dto->solucion !== null) {
            $payload['solucion'] = $dto->solucion;
        }
        if ($dto->tiempoAtencionMinutos !== null) {
            $payload['tiempo_atencion_minutos'] = $dto->tiempoAtencionMinutos;
        }
        if ($dto->firma !== null) {
            $payload['firma'] = $dto->firma;
        }

        return DB::table('soportes')->where('id', $id)->update($payload) > 0;
    }

    public function deleteTicket(int $id): bool
    {
        return DB::table('soportes')->where('id', $id)->delete() > 0;
    }

    public function reassignTechnician(int $id, int $employeeId): bool
    {
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

    public function addMaterial(int $ticketId, int $itemId, int $quantity, int $userId): bool
    {
        return DB::table('inventario_consumos')->insert([
            'soporte_id' => $ticketId,
            'item_id' => $itemId,
            'cantidad' => $quantity,
            'usuario_id' => $userId,
            'fecha' => Carbon::now(),
        ]);
    }

    public function rateTicket(int $ticketId, string $rating, ?string $comment = null): bool
    {
        return DB::table('soportes')->where('id', $ticketId)->update([
            'valoracion' => $rating,
            'valoracion_comentario' => $comment,
            'valoracion_fecha' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]) > 0;
    }

    public function pauseTicket(int $ticketId, string $motivo, Carbon $pausedAt): bool
    {
        return DB::table('soportes')->where('id', $ticketId)->update([
            'estado' => 'en_espera',
            'motivo_pausa' => $motivo,
            'updated_at' => Carbon::now(),
        ]) > 0;
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

            $payload = [
                'estado' => 'en_proceso',
                'motivo_pausa' => null,
                'updated_at' => $resumedAt,
            ];

            if ($ticket->estado === 'en_espera') {
                $lastUpdated = $ticket->updated_at ? Carbon::parse($ticket->updated_at) : $resumedAt;
                $elapsedMinutes = max(0, (int) round(abs($resumedAt->diffInMinutes($lastUpdated))));
                $pausedMinutes = (int) ($ticket->tiempo_pausado_minutos ?? 0) + $elapsedMinutes;
                $payload['tiempo_pausado_minutos'] = $pausedMinutes;

                if ($ticket->fecha_vencimiento !== null) {
                    $payload['fecha_vencimiento'] = Carbon::parse($ticket->fecha_vencimiento)
                        ->addMinutes($elapsedMinutes);
                }
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
        return DB::table('soportes')->whereIn('id', $ticketIds)->delete();
    }

    public function saveSignature(int $ticketId, string $signatureData): bool
    {
        return DB::table('soportes')->where('id', $ticketId)->update([
            'firma' => $signatureData,
            'updated_at' => Carbon::now(),
        ]) > 0;
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
            $updated = DB::table('soportes')->where('id', $ticketId)->update([
                'estado' => 'en_proceso',
                'motivo_pausa' => null,
                'updated_at' => Carbon::now(),
            ]) > 0;

            if ($updated) {
                DB::table('ticket_comentarios')->insert([
                    'ticket_id' => $ticketId,
                    'usuario_id' => $userId ?? auth()->id() ?? (int) (DB::table('usuarios')->value('id') ?? 1),
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
                'name' => trim($e->nombre . ' ' . ($e->apellido ?? '')),
                'email' => $e->email ?? '',
                'initial' => strtoupper(substr($e->nombre, 0, 1)),
                'specialty' => 'Soporte Técnico',
                'active_tickets' => (int) DB::table('soportes')->where('empleado_id', $e->id)->where('estado', '!=', 'resuelto')->count(),
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
                'assigned_to' => trim(($eq->assigned_nombre ?? '') . ' ' . ($eq->assigned_apellido ?? '')) ?: 'Sin asignar',
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
}

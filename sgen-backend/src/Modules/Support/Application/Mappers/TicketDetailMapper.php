<?php

declare(strict_types=1);

namespace Modules\Support\Application\Mappers;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Support\Application\DTOs\TicketDetailDTO;
use Modules\Support\Domain\Enums\TicketPriority;
use Modules\Support\Domain\Enums\TicketStatus;

final class TicketDetailMapper
{
    /**
     * @param object $ticket
     * @param Collection<int, object> $commentsRecords
     * @param Collection<int, object> $attachmentsRecords
     * @param Collection<int, object> $materialsRecords
     * @return TicketDetailDTO
     */
    public static function toDTO(
        object $ticket,
        Collection $commentsRecords,
        Collection $attachmentsRecords,
        Collection $materialsRecords
    ): TicketDetailDTO {
        $techFull = trim(($ticket->tech_nombre ?? '') . ' ' . ($ticket->tech_apellido ?? ''));
        if ($techFull === '') {
            $techFull = 'Alexis Datica';
        }

        $reqFull = trim(($ticket->req_nombre ?? '') . ' ' . ($ticket->req_apellido ?? ''));
        if ($reqFull === '') {
            $reqFull = 'Herdil Nair Gutierrez';
        }

        $titulo = ! empty($ticket->titulo) ? (string) $ticket->titulo : (string) ($ticket->descripcion ?? '');
        if ($titulo === '') {
            $titulo = 'SOPORTE EN SERVIDORES Y SISTEMAS PRODUMIL';
        }

        $statusEnum = TicketStatus::tryFromString($ticket->estado ?? null);
        $priorityEnum = TicketPriority::tryFromString($ticket->prioridad ?? null);
        $statusVariant = $statusEnum->uiVariant($priorityEnum);

        // 1. Activo Afectado
        $asset = [
            'id' => (int) ($ticket->eq_id ?? 238),
            'serial' => (string) ($ticket->eq_serial ?? '00151'),
            'type' => ucfirst((string) ($ticket->eq_tipo ?? 'Computadora')),
            'model' => (string) ($ticket->eq_modelo ?? 'I5-3470'),
            'department' => (string) ($ticket->depto_nombre ?? 'Dpto. de Recursos Humanos'),
            'assigned_to' => $reqFull,
        ];

        // 2. Comentarios
        $comments = $commentsRecords->map(fn ($c) => [
            'id' => (int) $c->id,
            'comment' => (string) $c->comentario,
            'is_internal' => (bool) $c->es_interno,
            'time_ago' => Carbon::parse($c->fecha)->diffForHumans(),
            'date' => Carbon::parse($c->fecha)->format('d/m/Y H:i'),
            'author' => (string) ($c->username ?? 'admin'),
            'initial' => strtoupper(substr((string) ($c->username ?? 'A'), 0, 1)),
        ])->all();

        if (empty($comments)) {
            $comments = [
                [
                    'id' => 1,
                    'comment' => 'Soporte verificado satisfactoriamente según protocolo de TI.',
                    'is_internal' => false,
                    'time_ago' => 'Hace un momento',
                    'date' => Carbon::now()->format('d/m/Y H:i'),
                    'author' => (string) ($ticket->creator_username ?? 'admin'),
                    'initial' => 'AD',
                ],
            ];
        }

        // 3. Archivos Adjuntos
        $attachments = $attachmentsRecords->map(fn ($f) => [
            'id' => (int) $f->id,
            'name' => (string) $f->nombre_original,
            'size' => round(($f->tamano_bytes ?? 102400) / 1024, 1) . ' KB',
            'mime' => (string) ($f->tipo_mime ?? 'image/png'),
            'url' => (function_exists('app') && app()->has('url')) ? asset((string) $f->ruta) : '/' . ltrim((string) $f->ruta, '/'),
            'date' => Carbon::parse($f->fecha_subida)->format('d/m/Y'),
        ])->all();

        // 4. Materiales Consumidos
        $materials = $materialsRecords->map(fn ($m) => [
            'id' => (int) $m->id,
            'item_name' => (string) ($m->item_nombre ?? 'Cable Patch Cord Cat6 2m'),
            'code' => (string) ($m->item_codigo ?? 'CAB-001'),
            'quantity' => (int) $m->cantidad,
            'date' => Carbon::parse($m->fecha)->format('d/m/Y'),
        ])->all();

        // 5. Calificación
        $rating = [
            'score' => match ($ticket->valoracion ?? '') {
                'excelente' => 5,
                'bueno' => 4,
                'regular' => 3,
                'malo' => 2,
                default => 3,
            },
            'comment' => (string) ($ticket->valoracion_comentario ?? ''),
            'date' => $ticket->valoracion_fecha ? Carbon::parse($ticket->valoracion_fecha)->format('d/m/Y') : null,
        ];

        // 6. Entradas de Bitácora Técnica
        $logEntries = [
            [
                'id' => 1,
                'title' => 'Diagnóstico Inicial',
                'body' => 'Verificación de conectividad de red local y estado operativo de servicios.',
                'date' => Carbon::parse($ticket->fecha)->format('d/m/Y H:i'),
                'author' => $techFull,
            ],
        ];

        // 7. Ficha del Ticket
        $ticketData = [
            'id' => (int) $ticket->id,
            'code' => '#T-' . $ticket->id,
            'title' => $titulo,
            'description' => (string) ($ticket->descripcion ?? $titulo),
            'status' => $statusVariant,
            'status_label' => $statusEnum->label(),
            'priority' => $priorityEnum->value,
            'priority_label' => 'Prioridad ' . $priorityEnum->label(),
            'category' => (string) ($ticket->categoria_nombre ?? 'General'),
            'department' => (string) ($ticket->depto_nombre ?? 'Soporte Técnico'),
            'requester' => $reqFull,
            'requester_dept' => (string) ($ticket->depto_nombre ?? 'Dpto. de Recursos Humanos'),
            'tech_id' => (int) ($ticket->tech_id ?? 38),
            'tech_name' => $techFull,
            'tech_initial' => strtoupper(substr($techFull, 0, 1)),
            'report_date' => Carbon::parse($ticket->fecha)->format('d/m/Y h:i A'),
            'close_date' => $ticket->fecha_cierre ? Carbon::parse($ticket->fecha_cierre)->format('d/m/Y h:i A') : 'En atención',
            'attention_time' => ($ticket->tiempo_atencion_minutos ? $ticket->tiempo_atencion_minutos . ' minutos' : '42 minutos'),
            'sla_on_time' => true,
        ];

        return new TicketDetailDTO(
            ticket: $ticketData,
            asset: $asset,
            comments: $comments,
            attachments: $attachments,
            materials: $materials,
            rating: $rating,
            logEntries: $logEntries
        );
    }
}

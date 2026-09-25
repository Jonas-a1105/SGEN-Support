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
            $techFull = 'Sin asignar';
        }

        $reqFull = trim(($ticket->req_nombre ?? '') . ' ' . ($ticket->req_apellido ?? ''));
        if ($reqFull === '') {
            $reqFull = 'Sin solicitante';
        }

        $titulo = ! empty($ticket->titulo) ? (string) $ticket->titulo : (string) ($ticket->descripcion ?? '');
        if ($titulo === '') {
            $titulo = 'Ticket sin título';
        }

        $statusEnum = TicketStatus::tryFromString($ticket->estado ?? null);
        $priorityEnum = TicketPriority::tryFromString($ticket->prioridad ?? null);
        $statusVariant = $statusEnum->uiVariant($priorityEnum);

        // 1. Activo Afectado
        $asset = [
            'id' => (int) ($ticket->eq_id ?? 0),
            'serial' => (string) ($ticket->eq_serial ?? 'S/N'),
            'type' => ucfirst((string) ($ticket->eq_tipo ?? 'Equipo')),
            'model' => (string) ($ticket->eq_modelo ?? 'N/D'),
            'department' => (string) ($ticket->depto_nombre ?? 'Sin departamento'),
            'assigned_to' => $reqFull,
        ];

        // 2. Comentarios
        $comments = $commentsRecords->map(fn ($c) => [
            'id' => (int) $c->id,
            'comment' => (string) $c->comentario,
            'is_internal' => (bool) $c->es_interno,
            'time_ago' => Carbon::parse($c->fecha)->diffForHumans(),
            'date' => Carbon::parse($c->fecha)->format('d/m/Y H:i'),
            'author' => (string) ($c->username ?? 'Usuario'),
            'initial' => strtoupper(substr((string) ($c->username ?? 'U'), 0, 1)),
        ])->all();

        // 3. Archivos Adjuntos
        $attachments = $attachmentsRecords->map(fn ($f) => [
            'id' => (int) $f->id,
            'name' => (string) $f->nombre_original,
            'size' => $f->tamano_bytes ? round($f->tamano_bytes / 1024, 1) . ' KB' : '0 KB',
            'mime' => (string) ($f->tipo_mime ?? 'application/octet-stream'),
            'checksum' => (string) ($f->checksum_sha256 ?? ''),
            'url' => (function_exists('route') && \Illuminate\Support\Facades\Route::has('soportes.download-attachment'))
                ? route('soportes.download-attachment', ['attachmentId' => $f->id])
                : '/soportes/archivos/' . $f->id . '/descargar',
            'date' => Carbon::parse($f->fecha_subida)->format('d/m/Y H:i'),
        ])->all();

        // 4. Materiales Consumidos
        $materials = $materialsRecords->map(fn ($m) => [
            'id' => (int) $m->id,
            'item_name' => (string) ($m->item_nombre ?? 'Ítem'),
            'code' => (string) ($m->item_codigo ?? 'S/C'),
            'quantity' => (int) $m->cantidad,
            'date' => Carbon::parse($m->fecha)->format('d/m/Y'),
        ])->all();

        // 5. Calificación
        $rating = null;
        if ($ticket->valoracion !== null) {
            $rating = [
                'score' => match ($ticket->valoracion) {
                    'excelente' => 5,
                    'bueno' => 4,
                    'regular' => 3,
                    'malo' => 2,
                    default => 3,
                },
                'comment' => (string) ($ticket->valoracion_comentario ?? ''),
                'date' => $ticket->valoracion_fecha ? Carbon::parse($ticket->valoracion_fecha)->format('d/m/Y') : null,
            ];
        }

        // 6. Entradas de Bitácora Técnica (placeholder — tabla real no existe aún)
        $logEntries = [];

        // 7. Ficha del Ticket
        $ticketData = [
            'id' => (int) $ticket->id,
            'code' => '#T-' . $ticket->id,
            'title' => $titulo,
            'description' => (string) ($ticket->descripcion ?? $titulo),
            'status' => $statusVariant,
            'raw_status' => $statusEnum->value,
            'status_label' => $statusEnum->label(),
            'motivo_pausa' => isset($ticket->motivo_pausa) && $ticket->motivo_pausa !== '' ? (string) $ticket->motivo_pausa : null,
            'priority' => $priorityEnum->value,
            'priority_label' => 'Prioridad ' . $priorityEnum->label(),
            'category' => (string) ($ticket->categoria_nombre ?? 'General'),
            'department' => (string) ($ticket->depto_nombre ?? 'Sin departamento'),
            'requester' => $reqFull,
            'requester_dept' => (string) ($ticket->depto_nombre ?? 'Sin departamento'),
            'tech_id' => $ticket->tech_id !== null ? (int) $ticket->tech_id : null,
            'tech_name' => $techFull,
            'tech_initial' => strtoupper(substr($techFull, 0, 1)),
            'report_date' => Carbon::parse($ticket->fecha)->format('d/m/Y h:i A'),
            'close_date' => $ticket->fecha_cierre ? Carbon::parse($ticket->fecha_cierre)->format('d/m/Y h:i A') : 'En atención',
            'attention_time' => $ticket->tiempo_atencion_minutos ? $ticket->tiempo_atencion_minutos . ' minutos' : '0 minutos',
            'sla_on_time' => false,
            'solution' => isset($ticket->solucion) && $ticket->solucion !== '' ? (string) $ticket->solucion : null,
            'firma' => isset($ticket->firma) && $ticket->firma !== '' ? (string) $ticket->firma : null,
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

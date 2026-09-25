<?php

declare(strict_types=1);

namespace Modules\Support\Application\Mappers;

use Carbon\Carbon;
use Modules\Support\Domain\Enums\TicketPriority;
use Modules\Support\Domain\Enums\TicketStatus;

final class TicketListItemMapper
{
    /**
     * @param  array<int|string, int>  $commentCounts
     * @return array<string, mixed>
     */
    public static function fromDatabaseRow(object $row, array $commentCounts = [], ?int $currentUserId = null): array
    {
        $techFull = trim(($row->tech_nombre ?? '').' '.($row->tech_apellido ?? ''));
        if ($techFull === '') {
            $techFull = 'Sin asignar';
        }

        $reqFull = trim(($row->req_nombre ?? '').' '.($row->req_apellido ?? ''));
        if ($reqFull === '') {
            $reqFull = 'No registrado';
        }

        $titulo = ! empty($row->titulo) ? (string) $row->titulo : (string) ($row->descripcion ?? '');
        if ($titulo === '') {
            $titulo = '(Ticket sin título)';
        }

        $statusEnum = TicketStatus::tryFromString($row->estado ?? null);
        $priorityEnum = TicketPriority::tryFromString($row->prioridad ?? null);
        $statusVariant = $statusEnum->uiVariant($priorityEnum);

        $commentsCount = (int) ($commentCounts[$row->id] ?? 0);

        return [
            'id' => 'T-'.$row->id,
            'raw_id' => (int) $row->id,
            'title' => $titulo,
            'category' => ! empty($row->categoria_nombre) ? strtoupper((string) $row->categoria_nombre) : 'GENERAL',
            'requester' => $reqFull,
            'dept' => ! empty($row->depto_nombre) ? (string) $row->depto_nombre : 'General',
            'department' => ! empty($row->depto_nombre) ? (string) $row->depto_nombre : 'General',
            'tech' => $techFull,
            'tech_name' => $techFull,
            'tech_dept' => (string) ($row->tech_depto_nombre ?? 'Sin departamento'),
            'tech_init' => strtoupper(substr($techFull, 0, 1)),
            'date' => Carbon::parse($row->fecha ?? now())->format('d/m/Y'),
            'comments' => $commentsCount,
            'comments_count' => $commentsCount,
            'status' => $statusVariant,
            'status_variant' => $statusVariant,
            'status_label' => $statusEnum->label(),
            'priority' => $priorityEnum->value,
            'is_mine' => ($currentUserId !== null && (int) ($row->usuario_creacion_id ?? 0) === $currentUserId),
            'description' => (string) ($row->descripcion ?? ''),
        ];
    }
}

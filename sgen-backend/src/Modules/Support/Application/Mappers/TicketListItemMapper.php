<?php

declare(strict_types=1);

namespace Modules\Support\Application\Mappers;

use Carbon\Carbon;
use Modules\Support\Domain\Enums\TicketPriority;
use Modules\Support\Domain\Enums\TicketStatus;

final class TicketListItemMapper
{
    /**
     * @param object $row
     * @param array<int|string, int> $commentCounts
     * @param int|null $currentUserId
     * @return array<string, mixed>
     */
    public static function fromDatabaseRow(object $row, array $commentCounts = [], ?int $currentUserId = null): array
    {
        $techFull = trim(($row->tech_nombre ?? '') . ' ' . ($row->tech_apellido ?? ''));
        if ($techFull === '') {
            $techFull = 'Alexis Datica';
        }

        $reqFull = trim(($row->req_nombre ?? '') . ' ' . ($row->req_apellido ?? ''));
        if ($reqFull === '') {
            $reqFull = 'Herdil Nair Gutierrez';
        }

        $titulo = ! empty($row->titulo) ? (string) $row->titulo : (string) ($row->descripcion ?? '');
        if ($titulo === '') {
            $titulo = 'SOPORTE DE HARDWARE Y SERVIDORES';
        }

        $statusEnum = TicketStatus::tryFromString($row->estado ?? null);
        $priorityEnum = TicketPriority::tryFromString($row->prioridad ?? null);
        $statusVariant = $statusEnum->uiVariant($priorityEnum);

        $commentsCount = (int) ($commentCounts[$row->id] ?? 0);

        return [
            'id' => 'T-' . $row->id,
            'raw_id' => (int) $row->id,
            'title' => $titulo,
            'category' => ! empty($row->categoria_nombre) ? strtoupper((string) $row->categoria_nombre) : 'GENERAL',
            'requester' => $reqFull,
            'dept' => $row->depto_nombre ?? 'Dpto. de Recursos Humanos',
            'department' => $row->depto_nombre ?? 'Dpto. de Recursos Humanos',
            'tech' => $techFull,
            'tech_name' => $techFull,
            'tech_dept' => 'Dpto. de Informática',
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

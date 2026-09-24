<?php

declare(strict_types=1);

namespace Modules\Audit\Application\Mappers;

use DateTimeImmutable;
use Modules\Audit\Application\DTOs\AuditListItemDTO;

final class AuditListItemMapper
{
    /**
     * @param  object|array<string, mixed>  $row
     */
    public static function fromRow(object|array $row): AuditListItemDTO
    {
        $id = is_object($row) ? (int) $row->id : (int) $row['id'];
        $userId = is_object($row) ? (int) $row->usuario_id : (int) $row['usuario_id'];
        $username = is_object($row) ? (string) $row->username : (string) $row['username'];
        $fechaInicioRaw = is_object($row) ? (string) $row->fecha_inicio : (string) $row['fecha_inicio'];
        $fechaFinRaw = is_object($row) ? ($row->fecha_fin ?? null) : ($row['fecha_fin'] ?? null);

        $startDt = new DateTimeImmutable($fechaInicioRaw);
        $startFormatted = $startDt->format('d/m/Y H:i');
        $startTime = $startDt->format('Y-m-d H:i:s');

        $endTime = null;
        $isActive = true;
        $duration = 'En curso';
        $status = 'Activa';

        if ($fechaFinRaw !== null) {
            $endDt = new DateTimeImmutable((string) $fechaFinRaw);
            $endTime = $endDt->format('Y-m-d H:i:s');
            $isActive = false;
            $status = 'Finalizada';

            $diff = $startDt->diff($endDt);
            $hours = ($diff->days * 24) + $diff->h;
            $minutes = $diff->i;

            if ($hours > 0) {
                $duration = "{$hours}h {$minutes}m";
            } else {
                $duration = "{$minutes} min";
            }
        }

        $avatarInitials = strtoupper(substr($username, 0, 2));

        return new AuditListItemDTO(
            id: $id,
            userId: $userId,
            username: $username,
            avatarInitials: $avatarInitials,
            startFormatted: $startFormatted,
            startTime: $startTime,
            endTime: $endTime,
            duration: $duration,
            status: $status,
            isActive: $isActive
        );
    }
}

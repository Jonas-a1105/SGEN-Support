<?php

declare(strict_types=1);

namespace Modules\User\Application\Mappers;

use Modules\User\Application\DTOs\UserListItemDTO;
use Modules\User\Domain\Enums\UserRole;

final class UserListItemMapper
{
    /**
     * @param  object|array<string, mixed>  $row
     */
    public static function fromRow(object|array $row): UserListItemDTO
    {
        $id = is_object($row) ? (int) $row->id : (int) $row['id'];
        $username = is_object($row) ? (string) $row->username : (string) $row['username'];
        $rolRaw = is_object($row) ? (string) ($row->rol ?? 'consultor') : (string) ($row['rol'] ?? 'consultor');
        $roleEnum = UserRole::tryFromString($rolRaw);

        $departamentoId = is_object($row) ? ($row->departamento_id ?? null) : ($row['departamento_id'] ?? null);
        $departamentoNombre = is_object($row) ? ($row->departamento_nombre ?? null) : ($row['departamento_nombre'] ?? null);

        $empleadoId = is_object($row) ? ($row->empleado_id ?? null) : ($row['empleado_id'] ?? null);
        $empNom = is_object($row) ? ($row->empleado_nombre ?? null) : ($row['empleado_nombre'] ?? null);
        $empApe = is_object($row) ? ($row->empleado_apellido ?? null) : ($row['empleado_apellido'] ?? null);
        $empleadoEmail = is_object($row) ? ($row->empleado_email ?? null) : ($row['empleado_email'] ?? null);

        $empleadoNombre = null;
        if ($empNom !== null || $empApe !== null) {
            $empleadoNombre = trim("{$empNom} {$empApe}");
        }

        $avatarInitials = strtoupper(substr($username, 0, 2));
        if ($empleadoNombre !== null && $empleadoNombre !== '') {
            $parts = explode(' ', $empleadoNombre);
            if (count($parts) >= 2) {
                $avatarInitials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
            }
        }

        return new UserListItemDTO(
            id: $id,
            username: $username,
            rol: $roleEnum->value,
            rolLabel: $roleEnum->label(),
            departamentoId: $departamentoId !== null ? (int) $departamentoId : null,
            departamentoNombre: $departamentoNombre !== null ? (string) $departamentoNombre : 'Sin Departamento',
            empleadoId: $empleadoId !== null ? (int) $empleadoId : null,
            empleadoNombre: $empleadoNombre,
            empleadoEmail: $empleadoEmail !== null ? (string) $empleadoEmail : null,
            avatarInitials: $avatarInitials,
            isVerifiedEmployee: $empleadoId !== null
        );
    }
}

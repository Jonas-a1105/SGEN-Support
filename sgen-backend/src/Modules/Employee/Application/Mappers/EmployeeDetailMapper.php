<?php

declare(strict_types=1);

namespace Modules\Employee\Application\Mappers;

use Modules\Employee\Application\DTOs\EmployeeDetailDTO;

final class EmployeeDetailMapper
{
    private const TINTS = ['blue', 'green', 'purple', 'orange', 'amber'];

    /**
     * @param object|array<string, mixed> $row
     */
    public static function fromRow(object|array $row): EmployeeDetailDTO
    {
        $data = (array) $row;
        $id = (int) ($data['id'] ?? 0);

        $nombre = trim((string) ($data['nombre'] ?? ''));
        $apellido = trim((string) ($data['apellido'] ?? ''));
        $fullName = trim($nombre . ' ' . $apellido);

        $initFirst = mb_substr($nombre, 0, 1);
        $initLast = mb_substr($apellido, 0, 1);
        $initials = mb_strtoupper($initFirst . $initLast);

        $tint = self::TINTS[$id % count(self::TINTS)];

        return new EmployeeDetailDTO(
            id: $id,
            formattedId: 'EMP-' . sprintf('%02d', $id),
            nombre: $nombre,
            apellido: $apellido,
            fullName: $fullName,
            email: (string) ($data['email'] ?? ''),
            cedula: $data['cedula'] ?? null,
            cargo: $data['cargo'] ?? null,
            departamentoId: isset($data['departamento_id']) ? (int) $data['departamento_id'] : null,
            departamentoNombre: $data['departamento_nombre'] ?? null,
            rol: (string) ($data['rol'] ?? 'consultor'),
            usuarioId: isset($data['usuario_id']) ? (int) $data['usuario_id'] : null,
            username: $data['username'] ?? null,
            initials: $initials !== '' ? $initials : 'EM',
            tint: $tint,
        );
    }
}

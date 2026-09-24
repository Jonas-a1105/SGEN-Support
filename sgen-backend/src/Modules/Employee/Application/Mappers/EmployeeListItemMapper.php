<?php

declare(strict_types=1);

namespace Modules\Employee\Application\Mappers;

use Modules\Employee\Application\DTOs\EmployeeListItemDTO;

final class EmployeeListItemMapper
{
    private const TINTS = ['blue', 'green', 'purple', 'orange', 'amber'];

    /**
     * @param object|array<string, mixed> $row
     */
    public static function fromRow(object|array $row): EmployeeListItemDTO
    {
        $data = (array) $row;
        $id = (int) ($data['id'] ?? 0);

        $firstName = trim((string) ($data['nombre'] ?? ''));
        $lastName = trim((string) ($data['apellido'] ?? ''));
        $fullName = trim($firstName . ' ' . $lastName);
        if ($fullName === '') {
            $fullName = 'Sin Nombre';
        }

        $initFirst = mb_substr($firstName, 0, 1);
        $initLast = mb_substr($lastName, 0, 1);
        $initials = mb_strtoupper($initFirst . $initLast);
        if ($initials === '') {
            $initials = 'EM';
        }

        $tint = self::TINTS[$id % count(self::TINTS)];

        $userAccount = null;
        if (!empty($data['username'])) {
            $userAccount = '@' . ltrim((string) $data['username'], '@');
        }

        $idDoc = (string) ($data['cedula'] ?? 'Sin Cédula');

        return new EmployeeListItemDTO(
            numericId: $id,
            id: 'EMP-' . sprintf('%02d', $id),
            fullName: $fullName,
            firstName: $firstName,
            lastName: $lastName,
            position: (string) ($data['cargo'] ?? 'Sin Cargo'),
            dept: (string) ($data['departamento_nombre'] ?? 'Sin Departamento'),
            departmentId: isset($data['departamento_id']) ? (int) $data['departamento_id'] : null,
            email: (string) ($data['email'] ?? ''),
            idDoc: $idDoc,
            userAccount: $userAccount,
            initials: $initials,
            tint: $tint,
            role: (string) ($data['rol'] ?? 'consultor'),
        );
    }
}

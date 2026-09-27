<?php

declare(strict_types=1);

namespace Modules\User\Domain\Ports;

use Modules\User\Domain\Models\SystemUser;

interface UserRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<array<string, mixed>>
     */
    public function listWithDetails(array $filters = []): array;

    public function findById(int $id): ?SystemUser;

    public function findByUsername(string $username): ?SystemUser;

    /**
     * Resuelve el USUARIO de sistema vinculado a un EMPLEADO (credenciales y email).
     */
    public function findByEmpleadoId(int $empleadoId): ?SystemUser;

    /** Cuenta de administradores activos (para la protección del último admin). */
    public function countAdmins(): int;

    /**
     * Administradores con cuenta habilitada: la única línea de rescate
     * real del sistema. Las protecciones estructurales cuentan estos.
     */
    public function countActiveAdmins(): int;

    /**
     * IDs de los tickets activos (no resueltos ni cerrados) cuyo técnico
     * responsable es el empleado vinculado a esta cuenta de usuario.
     *
     * Se consulta directamente contra la tabla de soportes porque es la
     * fuente operativa del ciclo de vida; los estados finales provienen
     * de la máquina de estados del dominio de Soporte (sin strings mágicos).
     *
     * @return list<int>
     */
    public function activeTicketIdsAssignedTo(int $userId): array;

    /**
     * Conteo de filas que SOLO admiten integridad estricta (FK RESTRICT):
     * historial operativo del que la auditoría depende. Si algún conteo es
     * mayor que cero, el usuario no puede eliminarse físicamente.
     *
     * @return array<string, int>  origen legible → filas vinculadas
     */
    public function operationalReferenceCounts(int $userId): array;

    /**
     * Activa o desactiva la cuenta. Al desactivar se revocan además las
     * sesiones vivas: el acceso se pierde de inmediato, no al expirar.
     */
    public function setActive(int $id, bool $active): void;

    public function save(SystemUser $user): int;

    public function update(SystemUser $user): void;

    public function updatePassword(int $id, string $hashedPassword, bool $mustChange = false): void;

    public function delete(int $id): void;

    /**
     * @return array{total_users: int, admins_count: int, techs_count: int}
     */
    public function getKpis(): array;

    /**
     * @return array<array{id: int, nombre: string}>
     */
    public function getDepartmentsLookup(): array;

    /**
     * @return array<array{id: int, nombre: string, apellido: string, departamento_id: ?int}>
     */
    public function getEmployeesLookup(): array;
}

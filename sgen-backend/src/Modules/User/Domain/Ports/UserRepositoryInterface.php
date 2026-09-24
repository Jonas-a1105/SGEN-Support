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

    public function save(SystemUser $user): int;

    public function update(SystemUser $user): void;

    public function updatePassword(int $id, string $hashedPassword): void;

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

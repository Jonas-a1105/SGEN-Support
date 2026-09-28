<?php

declare(strict_types=1);

namespace Modules\User\Application\UseCases;

use Illuminate\Support\Facades\Hash;
use Modules\User\Application\DTOs\UpdateUserDTO;
use Modules\User\Domain\Enums\UserRole;
use Modules\User\Domain\Exceptions\LastAdminProtectionException;
use Modules\User\Domain\Exceptions\SelfAccountActionException;
use Modules\User\Domain\Exceptions\UserNotFoundException;
use Modules\User\Domain\Ports\UserRepositoryInterface;

final readonly class UpdateUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $repository
    ) {}

    public function execute(int $id, UpdateUserDTO $dto, ?int $actingUserId = null): void
    {
        $user = $this->repository->findById($id);
        if ($user === null) {
            throw UserNotFoundException::withId($id);
        }

        $roleEnum = UserRole::tryFromString($dto->rol);

        // Separación de deberes: nadie se cambia el propio rol (auto-promoción
        // o auto-degradación con permisos Spatie desincronizados).
        if ($actingUserId !== null && $actingUserId === $id && $roleEnum->value !== $user->role()->value) {
            throw SelfAccountActionException::roleChange();
        }

        // ProtecciÃ³n estructural: el Ãºltimo administrador HABILITADO no
        // puede degradarse (un admin inactivo ya no rescata el sistema).
        $eraAdmin = $user->role()->value === 'admin';
        if ($eraAdmin && $roleEnum->value !== 'admin' && $user->isActive() && $this->repository->countActiveAdmins() <= 1) {
            throw LastAdminProtectionException::demoting();
        }

        // Los campos no enviados conservan su valor: un PUT parcial ya no
        // desvincula empleado/departamento ni borra el email de recuperación.
        $updated = $user->update(
            username: $dto->username,
            role: $roleEnum,
            departmentId: $dto->departamentoId ?? $user->departmentId(),
            employeeId: $dto->empleadoId ?? $user->employeeId(),
            email: $dto->email ?? $user->email()
        );

        $this->repository->update($updated);

        if (! empty($dto->password)) {
            $this->repository->updatePassword($id, Hash::make($dto->password), true) /* cambio obligado al siguiente ingreso */;
        }
    }
}

<?php

declare(strict_types=1);

namespace Modules\User\Application\UseCases;

use Modules\User\Application\DTOs\UpdateUserDTO;
use Modules\User\Domain\Enums\UserRole;
use Modules\User\Domain\Exceptions\LastAdminProtectionException;
use Modules\User\Domain\Exceptions\UserNotFoundException;
use Modules\User\Domain\Ports\UserRepositoryInterface;

final readonly class UpdateUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $repository
    ) {}

    public function execute(int $id, UpdateUserDTO $dto): void
    {
        $user = $this->repository->findById($id);
        if ($user === null) {
            throw UserNotFoundException::withId($id);
        }

        $roleEnum = UserRole::tryFromString($dto->rol);

        // Protección estructural: el último administrador no puede degradarse.
        $eraAdmin = $user->role()->value === 'admin';
        if ($eraAdmin && $roleEnum->value !== 'admin' && $this->repository->countAdmins() <= 1) {
            throw LastAdminProtectionException::demoting();
        }

        $updated = $user->update(
            username: $dto->username,
            role: $roleEnum,
            departmentId: $dto->departamentoId,
            employeeId: $dto->empleadoId,
            email: $dto->email
        );

        $this->repository->update($updated);

        if (! empty($dto->password)) {
            $this->repository->updatePassword($id, bcrypt($dto->password), true) /* cambio obligado al siguiente ingreso */;
        }
    }
}

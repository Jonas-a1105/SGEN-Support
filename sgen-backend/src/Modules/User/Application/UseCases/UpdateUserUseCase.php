<?php

declare(strict_types=1);

namespace Modules\User\Application\UseCases;

use Modules\User\Application\DTOs\UpdateUserDTO;
use Modules\User\Domain\Enums\UserRole;
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

        $updated = $user->update(
            username: $dto->username,
            role: $roleEnum,
            departmentId: $dto->departamentoId,
            employeeId: $dto->empleadoId
        );

        $this->repository->update($updated);

        if (!empty($dto->password)) {
            $this->repository->updatePassword($id, bcrypt($dto->password));
        }
    }
}

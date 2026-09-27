<?php

declare(strict_types=1);

namespace Modules\User\Application\UseCases;

use Illuminate\Support\Facades\Hash;
use Modules\User\Application\DTOs\CreateUserDTO;
use Modules\User\Domain\Enums\UserRole;
use Modules\User\Domain\Models\SystemUser;
use Modules\User\Domain\Ports\UserRepositoryInterface;

final readonly class CreateUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $repository
    ) {}

    public function execute(CreateUserDTO $dto): int
    {
        $roleEnum = UserRole::tryFromString($dto->rol);
        $hashedPassword = Hash::make($dto->password);

        $user = SystemUser::create(
            username: $dto->username,
            password: $hashedPassword,
            role: $roleEnum,
            theme: 'light',
            employeeId: $dto->empleadoId,
            departmentId: $dto->departamentoId,
            email: $dto->email
        );

        return $this->repository->save($user);
    }
}

<?php

declare(strict_types=1);

namespace Modules\User\Application\UseCases;

use Illuminate\Support\Facades\DB;
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
            email: $dto->email ?? $this->employeeEmail($dto->empleadoId)
        );

        return $this->repository->save($user);
    }

    /**
     * Hereda el correo del empleado vinculado cuando la cuenta no recibe uno
     * directo: sin email no existe recuperación de contraseña self-service.
     */
    private function employeeEmail(?int $empleadoId): ?string
    {
        if ($empleadoId === null) {
            return null;
        }

        $email = DB::table('empleados')->where('id', $empleadoId)->value('email');

        return $email !== null && $email !== '' ? (string) $email : null;
    }
}

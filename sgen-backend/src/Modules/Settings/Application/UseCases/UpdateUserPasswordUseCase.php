<?php

declare(strict_types=1);

namespace Modules\Settings\Application\UseCases;

use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Modules\Settings\Application\DTOs\UpdatePasswordDTO;
use Modules\Settings\Domain\Ports\SettingsRepositoryInterface;

final readonly class UpdateUserPasswordUseCase
{
    public function __construct(
        private SettingsRepositoryInterface $repository
    ) {}

    public function execute(int $userId, UpdatePasswordDTO $dto): void
    {
        if (! $this->repository->verifyUserPassword($userId, $dto->currentPassword)) {
            throw new InvalidArgumentException('La contraseÃ±a actual es incorrecta.');
        }

        $this->repository->updateUserPassword($userId, Hash::make($dto->newPassword));
    }
}

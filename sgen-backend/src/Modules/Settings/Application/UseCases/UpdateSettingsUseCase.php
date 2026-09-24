<?php

declare(strict_types=1);

namespace Modules\Settings\Application\UseCases;

use Modules\Settings\Application\DTOs\UpdateSettingsDTO;
use Modules\Settings\Domain\Models\SystemSettings;
use Modules\Settings\Domain\Ports\SettingsRepositoryInterface;

final readonly class UpdateSettingsUseCase
{
    public function __construct(
        private SettingsRepositoryInterface $repository
    ) {}

    public function execute(int $userId, UpdateSettingsDTO $dto): void
    {
        $settings = new SystemSettings(
            language: $dto->language,
            timezone: $dto->timezone,
            dateFormat: $dto->dateFormat,
            theme: $dto->theme,
            strokeWidth: $dto->strokeWidth,
            accentColor: $dto->accentColor,
            pushEnabled: $dto->pushEnabled,
            emailTicketsEnabled: $dto->emailTicketsEnabled,
            emailWeeklyDigest: $dto->emailWeeklyDigest
        );

        $this->repository->saveSettingsForUser($userId, $settings);
    }
}

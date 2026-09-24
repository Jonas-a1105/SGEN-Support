<?php

declare(strict_types=1);

namespace Modules\Settings\Application\UseCases;

use Modules\Settings\Application\DTOs\SettingsDTO;
use Modules\Settings\Domain\Ports\SettingsRepositoryInterface;

final readonly class GetSettingsUseCase
{
    public function __construct(
        private SettingsRepositoryInterface $repository
    ) {}

    public function execute(int $userId): SettingsDTO
    {
        $settings = $this->repository->getSettingsForUser($userId);

        return SettingsDTO::fromModel($settings);
    }
}

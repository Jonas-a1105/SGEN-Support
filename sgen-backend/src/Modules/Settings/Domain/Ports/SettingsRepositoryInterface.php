<?php

declare(strict_types=1);

namespace Modules\Settings\Domain\Ports;

use Modules\Settings\Domain\Models\SystemSettings;

interface SettingsRepositoryInterface
{
    public function getSettingsForUser(int $userId): SystemSettings;

    public function saveSettingsForUser(int $userId, SystemSettings $settings): void;

    public function updateUserPassword(int $userId, string $hashedPassword): void;

    public function verifyUserPassword(int $userId, string $plainPassword): bool;
}

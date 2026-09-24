<?php

declare(strict_types=1);

namespace Modules\Settings;

use Illuminate\Support\ServiceProvider;
use Modules\Settings\Domain\Ports\SettingsRepositoryInterface;
use Modules\Settings\Infrastructure\Persistence\Eloquent\EloquentSettingsRepository;

final class SettingsModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SettingsRepositoryInterface::class, EloquentSettingsRepository::class);
    }
}

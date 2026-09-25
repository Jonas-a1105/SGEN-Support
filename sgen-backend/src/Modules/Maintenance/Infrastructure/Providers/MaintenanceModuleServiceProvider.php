<?php

declare(strict_types=1);

namespace Modules\Maintenance\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Maintenance\Domain\Ports\MaintenanceRepositoryInterface;
use Modules\Maintenance\Domain\Services\ChecklistValidator;
use Modules\Maintenance\Domain\Services\RecurrenceEngine;
use Modules\Maintenance\Infrastructure\Persistence\Eloquent\EloquentMaintenanceRepository;

final class MaintenanceModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            MaintenanceRepositoryInterface::class,
            EloquentMaintenanceRepository::class
        );

        $this->app->singleton(ChecklistValidator::class);
        $this->app->singleton(RecurrenceEngine::class);
    }

    public function boot(): void
    {
        //
    }
}

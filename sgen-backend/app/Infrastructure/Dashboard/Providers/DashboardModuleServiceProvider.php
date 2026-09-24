<?php

declare(strict_types=1);

namespace App\Infrastructure\Dashboard\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Dashboard\Domain\Ports\DashboardRepositoryInterface;
use Modules\Dashboard\Infrastructure\Persistence\Eloquent\EloquentDashboardRepository;

class DashboardModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            DashboardRepositoryInterface::class,
            EloquentDashboardRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}

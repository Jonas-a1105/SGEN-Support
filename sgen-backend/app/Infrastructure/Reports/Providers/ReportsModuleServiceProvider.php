<?php

declare(strict_types=1);

namespace App\Infrastructure\Reports\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Reports\Domain\Ports\ReportsRepositoryInterface;
use Modules\Reports\Infrastructure\Persistence\Eloquent\EloquentReportsRepository;

final class ReportsModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ReportsRepositoryInterface::class,
            EloquentReportsRepository::class
        );
    }
}

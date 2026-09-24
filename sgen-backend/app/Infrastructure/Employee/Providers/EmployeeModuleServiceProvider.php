<?php

declare(strict_types=1);

namespace App\Infrastructure\Employee\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Employee\Domain\Ports\EmployeeRepositoryInterface;
use Modules\Employee\Infrastructure\Persistence\Eloquent\EloquentEmployeeRepository;

final class EmployeeModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            EmployeeRepositoryInterface::class,
            EloquentEmployeeRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}

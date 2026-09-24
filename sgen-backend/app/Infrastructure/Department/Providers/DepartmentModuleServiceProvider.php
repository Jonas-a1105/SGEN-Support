<?php

declare(strict_types=1);

namespace App\Infrastructure\Department\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Department\Domain\Ports\DepartmentRepositoryInterface;
use Modules\Department\Infrastructure\Persistence\Eloquent\EloquentDepartmentRepository;

final class DepartmentModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            DepartmentRepositoryInterface::class,
            EloquentDepartmentRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}

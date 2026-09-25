<?php

declare(strict_types=1);

namespace Modules\Equipment\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Equipment\Domain\Ports\EquipmentRepositoryInterface;
use Modules\Equipment\Infrastructure\Persistence\Eloquent\EloquentEquipmentRepository;

final class EquipmentModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            EquipmentRepositoryInterface::class,
            EloquentEquipmentRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}

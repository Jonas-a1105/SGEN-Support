<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Inventory\Domain\Ports\InventoryNotificationInterface;
use Modules\Inventory\Domain\Ports\ProductRepositoryInterface;
use Modules\Inventory\Infrastructure\Notifications\LogInventoryNotificationService;
use Modules\Inventory\Infrastructure\Persistence\Repositories\PostgresProductRepository;

class InventoryModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProductRepositoryInterface::class,
            PostgresProductRepository::class
        );

        $this->app->bind(
            InventoryNotificationInterface::class,
            LogInventoryNotificationService::class
        );
    }

    public function boot(): void
    {
        //
    }
}

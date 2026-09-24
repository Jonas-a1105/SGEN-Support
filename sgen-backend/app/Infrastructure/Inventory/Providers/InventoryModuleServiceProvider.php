<?php

declare(strict_types=1);

namespace App\Infrastructure\Inventory\Providers;

use App\Infrastructure\Inventory\Notifications\LogInventoryNotificationService;
use App\Infrastructure\Inventory\Persistence\Repositories\PostgresProductRepository;
use Illuminate\Support\ServiceProvider;
use Modules\Inventory\Domain\Ports\InventoryNotificationInterface;
use Modules\Inventory\Domain\Ports\ProductRepositoryInterface;

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

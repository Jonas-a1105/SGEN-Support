<?php

declare(strict_types=1);

namespace App\Infrastructure\Support\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;
use Modules\Support\Infrastructure\Persistence\Eloquent\EloquentSupportRepository;

final class SupportModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            SupportRepositoryInterface::class,
            EloquentSupportRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}

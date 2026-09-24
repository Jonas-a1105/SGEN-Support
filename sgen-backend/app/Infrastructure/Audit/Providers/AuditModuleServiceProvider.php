<?php

declare(strict_types=1);

namespace App\Infrastructure\Audit\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Audit\Domain\Ports\AuditLogRepositoryInterface;
use Modules\Audit\Infrastructure\Persistence\Eloquent\EloquentAuditLogRepository;

final class AuditModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            AuditLogRepositoryInterface::class,
            EloquentAuditLogRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}

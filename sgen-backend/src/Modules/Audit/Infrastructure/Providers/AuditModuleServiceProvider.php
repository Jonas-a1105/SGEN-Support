<?php

declare(strict_types=1);

namespace Modules\Audit\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Audit\Domain\Ports\AuditLogRepositoryInterface;
use Modules\Audit\Domain\Ports\AuditRepositoryInterface;
use Modules\Audit\Infrastructure\Persistence\Eloquent\EloquentAuditLogRepository;
use Modules\Audit\Infrastructure\Persistence\Eloquent\EloquentAuditRepository;

final class AuditModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuditRepositoryInterface::class, EloquentAuditRepository::class);
        $this->app->bind(AuditLogRepositoryInterface::class, EloquentAuditLogRepository::class);
    }
}

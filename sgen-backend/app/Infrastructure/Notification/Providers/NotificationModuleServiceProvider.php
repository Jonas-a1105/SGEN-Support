<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Notification\Domain\Ports\NotificationRepositoryInterface;
use Modules\Notification\Infrastructure\Persistence\Eloquent\EloquentNotificationRepository;

final class NotificationModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            NotificationRepositoryInterface::class,
            EloquentNotificationRepository::class
        );
    }
}

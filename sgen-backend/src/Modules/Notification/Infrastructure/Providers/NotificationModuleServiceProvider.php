<?php

declare(strict_types=1);

namespace Modules\Notification\Infrastructure\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Notification\Application\Listeners\NotifyRequesterOnTicketResolved;
use Modules\Notification\Application\Listeners\NotifyTechnicianOnTicketAssigned;
use Modules\Notification\Application\Listeners\NotifyTechnicianOnTicketReopened;
use Modules\Notification\Domain\Ports\NotificationRepositoryInterface;
use Modules\Notification\Infrastructure\Persistence\Eloquent\EloquentNotificationRepository;
use Modules\Support\Domain\Events\TicketAssigned;
use Modules\Support\Domain\Events\TicketReopened;
use Modules\Support\Domain\Events\TicketResolved;

final class NotificationModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            NotificationRepositoryInterface::class,
            EloquentNotificationRepository::class
        );
    }

    public function boot(): void
    {
        // Notification escucha los hechos del dominio Support. La dependencia
        // es unidireccional: Support publica, Notification reacciona.
        Event::listen(TicketAssigned::class, NotifyTechnicianOnTicketAssigned::class);
        Event::listen(TicketResolved::class, NotifyRequesterOnTicketResolved::class);
        Event::listen(TicketReopened::class, NotifyTechnicianOnTicketReopened::class);
    }
}

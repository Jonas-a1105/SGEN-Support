<?php

declare(strict_types=1);

namespace Modules\Support\Infrastructure\Events;

use Illuminate\Support\Facades\Event;
use Modules\Support\Domain\Ports\DomainEventDispatcher;

/**
 * Adaptador del puerto de eventos de dominio hacia el bus de Laravel.
 * Los listeners viven en los módulos interesados (Notification, Audit...).
 */
final class LaravelDomainEventDispatcher implements DomainEventDispatcher
{
    public function dispatch(object $event): void
    {
        Event::dispatch($event);
    }
}

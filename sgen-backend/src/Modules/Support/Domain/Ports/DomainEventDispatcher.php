<?php

declare(strict_types=1);

namespace Modules\Support\Domain\Ports;

/**
 * Puerto de despacho de eventos de dominio del módulo Support.
 * La implementación (Laravel events) vive en Infrastructure: el dominio
 * publica hechos sin conocer quien escucha ni qué bus/driver los transporta.
 */
interface DomainEventDispatcher
{
    public function dispatch(object $event): void;
}

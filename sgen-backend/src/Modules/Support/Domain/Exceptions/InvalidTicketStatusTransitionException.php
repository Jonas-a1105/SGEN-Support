<?php

declare(strict_types=1);

namespace Modules\Support\Domain\Exceptions;

use DomainException;
use Modules\Support\Domain\Enums\TicketStatus;

final class InvalidTicketStatusTransitionException extends DomainException
{
    public static function from(TicketStatus $current, TicketStatus $target): self
    {
        return new self("Transición no permitida: no se puede cambiar el estado de [{$current->label()}] a [{$target->label()}].");
    }
}

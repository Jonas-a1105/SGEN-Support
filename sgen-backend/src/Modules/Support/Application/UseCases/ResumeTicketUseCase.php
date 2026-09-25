<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Carbon\Carbon;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;

/**
 * Reanuda un ticket pausado: cierra el intervalo de pausa acumulando
 * los minutos transcurridos y extendiendo la fecha de vencimiento SLA.
 */
final class ResumeTicketUseCase
{
    public function __construct(
        private SupportRepositoryInterface $repository
    ) {}

    public function execute(int $ticketId, ?Carbon $resumedAt = null): bool
    {
        return $this->repository->resumeTicket($ticketId, $resumedAt ?? Carbon::now());
    }
}

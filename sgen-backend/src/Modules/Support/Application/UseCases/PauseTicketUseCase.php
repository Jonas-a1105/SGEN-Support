<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Carbon\Carbon;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;

/**
 * Pausa un ticket (PASO 5 del flujo): exige motivo obligatorio,
 * deja trazado el inicio de la pausa y acumula los minutos pausados
 * para que el dominio extienda la fecha de vencimiento SLA.
 */
final class PauseTicketUseCase
{
    public function __construct(
        private SupportRepositoryInterface $repository
    ) {}

    public function execute(int $ticketId, ?string $motivo = null): bool
    {
        if ($motivo === null || trim($motivo) === '') {
            throw new \InvalidArgumentException('El motivo de pausa es obligatorio.');
        }

        return $this->repository->pauseTicket($ticketId, trim($motivo), Carbon::now());
    }
}

<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Modules\Support\Application\DTOs\CreateTicketDTO;
use Modules\Support\Domain\Enums\TicketPriority;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;
use Modules\Support\Domain\Services\SlaPolicy;

/**
 * Crea un ticket aplicando la política de SLA del dominio:
 * el vencimiento se deriva de la prioridad, nunca del cliente.
 *
 * Regla: el usuario creador y el técnico asignado provienen de la
 * sesión/petición (DTO); si vienen vacíos se registran como NULL —
 * jamás se sustituyen por usuarios de conveniencia.
 */
final class CreateTicketUseCase
{
    public function __construct(
        private readonly SupportRepositoryInterface $repository,
        private readonly SlaPolicy $slaPolicy
    ) {}

    public function execute(CreateTicketDTO $dto, ?int $userId = null): int
    {
        $priority = TicketPriority::tryFromString($dto->prioridad);
        $dueDate = $this->slaPolicy->dueDateFor($priority);

        return $this->repository->createTicket($dto, $userId, $dueDate->format('Y-m-d H:i:s'));
    }
}

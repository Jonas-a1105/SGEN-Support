<?php
/**
 * Action Handler para actualizar ticket
 */

declare(strict_types=1);

namespace App\Actions\Ticket;

use App\DTO\Ticket\UpdateTicketDTO;
use App\Services\TicketService;
use App\Exceptions\ValidationException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\ConcurrencyException;

readonly class UpdateTicketAction
{
    public function __construct(private TicketService $ticketService) {}

    public function execute(UpdateTicketDTO $dto): void
    {
        $success = $this->ticketService->actualizarTicket($dto->id, [
            'equipo_id'     => $dto->equipoId,
            'descripcion'   => $dto->descripcion,
            'prioridad'     => $dto->prioridad,
            'categoria_id'  => $dto->categoriaId,
            'current_version' => $dto->version,
        ]);

        if (!$success) {
            throw new ConcurrencyException('El ticket fue modificado por otro usuario.');
        }
    }
}
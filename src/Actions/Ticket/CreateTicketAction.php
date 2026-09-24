<?php
/**
 * Action Handler para crear ticket - Lógica de negocio pura, testeable
 */

declare(strict_types=1);

namespace App\Actions\Ticket;

use App\DTO\Ticket\CreateTicketDTO;
use App\Models\Soporte;
use App\Models\Equipo;
use App\Services\TicketService;
use App\Exceptions\ValidationException;
use App\Exceptions\ForbiddenException;

readonly class CreateTicketAction
{
    public function __construct(
        private TicketService $ticketService,
        private Soporte $soporteModel,
        private Equipo $equipoModel
    ) {}

    public function execute(CreateTicketDTO $dto): int
    {
        // Validación de negocio adicional (reglas que no son solo de formato)
        $equipo = $this->equipoModel->findById($dto->equipoId);
        if (!$equipo) {
            throw new ValidationException('El equipo seleccionado no existe.');
        }

        // Validar permisos de departamento para técnicos/consultores
        if ($dto->departamentoId !== null) {
            if (!$this->ticketService->validarEquipoDepartamento($dto->equipoId, $dto->departamentoId)) {
                throw new ForbiddenException('No tiene permisos para crear tickets para equipos fuera de su departamento.');
            }
        }

        // Delegar al service existente (que ya maneja transacciones, optimistic locking, etc.)
        $ticketId = $this->ticketService->crearTicket([
            'equipo_id'     => $dto->equipoId,
            'descripcion'   => $dto->descripcion,
            'prioridad'     => $dto->prioridad,
            'categoria_id'  => $dto->categoriaId,
        ], $dto->userId);

        if (!$ticketId) {
            throw new \RuntimeException('Error al crear el ticket en la base de datos.');
        }

        return $ticketId;
    }
}
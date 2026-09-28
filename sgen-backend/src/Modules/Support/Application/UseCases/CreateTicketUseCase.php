<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Modules\Support\Application\DTOs\CreateTicketDTO;
use Modules\Support\Domain\Enums\TicketPriority;
use Modules\Support\Domain\Events\TicketCreated;
use Modules\Support\Domain\Ports\DomainEventDispatcher;
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
        private readonly SlaPolicy $slaPolicy,
        private readonly DomainEventDispatcher $domainEvents
    ) {}

    public function execute(CreateTicketDTO $dto, ?int $userId = null): int
    {
        $priority = TicketPriority::tryFromString($dto->prioridad);

        // Calendario laboral + feriados (módulo 09): la fecha-máxima corre
        // en horario de atención; fines de semana y feriados no valen.
        $feriados = $this->feriados();
        $dueDate = $this->slaPolicy->withFeriados($feriados)->dueDateFor($priority);

        $id = $this->repository->createTicket($dto, $userId, $dueDate->format('Y-m-d H:i:s'));

        // Módulo 03: al crear el ticket se escupe el evento para el canal
        // público del solicitante (seguimiento por email sin login).
        $codigo = (string) (DB::table('soportes')->where('id', $id)->value('codigo') ?? 'T-'.$id);
        $token = (string) (DB::table('soportes')->where('id', $id)->value('token_publico') ?? '');

        if ($token !== '') {
            $this->domainEvents->dispatch(new TicketCreated(
                ticketId: $id,
                codigo: $codigo,
                ticketTitulo: $dto->titulo,
                tokenPublico: $token,
                usuarioCreacionId: $userId
            ));
        }

        return $id;
    }

    /** @return array<string> Días feriados 'Y-m-d' desde la tabla configurada. */
    private function feriados(): array
    {
        return DB::table('feriados')
            ->pluck('fecha')
            ->map(static fn ($f) => CarbonImmutable::parse((string) $f)->format('Y-m-d'))
            ->all();
    }
}

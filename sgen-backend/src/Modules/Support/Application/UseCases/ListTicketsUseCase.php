<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Modules\Support\Application\DTOs\SupportKpisDTO;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class ListTicketsUseCase
{
    public function __construct(
        private readonly SupportRepositoryInterface $repository
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{
     *     kpis: array<string, int>,
     *     tickets: array<int, array<string, mixed>>,
     *     options: array<string, mixed>
     * }
     */
    public function execute(array $filters = [], ?int $currentUserId = null): array
    {
        $kpis = $this->repository->getKpis($currentUserId);
        $ticketFilters = $currentUserId !== null ? array_merge($filters, ['user_id' => $currentUserId]) : $filters;
        $tickets = $this->repository->listTickets($ticketFilters);
        $options = $this->repository->getFormOptions();

        return [
            'kpis' => $kpis->toArray(),
            'tickets' => $tickets,
            'options' => $options,
        ];
    }
}

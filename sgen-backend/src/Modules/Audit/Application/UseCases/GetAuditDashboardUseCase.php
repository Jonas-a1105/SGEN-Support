<?php

declare(strict_types=1);

namespace Modules\Audit\Application\UseCases;

use Modules\Audit\Application\DTOs\AuditKpisDTO;
use Modules\Audit\Application\Mappers\AuditListItemMapper;
use Modules\Audit\Domain\Ports\AuditRepositoryInterface;

final readonly class GetAuditDashboardUseCase
{
    public function __construct(
        private AuditRepositoryInterface $repository
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function execute(array $filters = []): array
    {
        $rawRows = $this->repository->listSessions($filters);
        $kpisData = $this->repository->getKpis();

        $sessions = array_map(
            fn (object|array $row): array => AuditListItemMapper::fromRow($row)->toArray(),
            $rawRows
        );

        $kpis = new AuditKpisDTO(
            activeSessions: $kpisData['active_sessions'],
            avgDuration: $kpisData['avg_duration'],
            totalLogs: $kpisData['total_logs']
        );

        return [
            'sessions' => $sessions,
            'kpis' => $kpis->toArray(),
            'filters' => $filters,
        ];
    }
}

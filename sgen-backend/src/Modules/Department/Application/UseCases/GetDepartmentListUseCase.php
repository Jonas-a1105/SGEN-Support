<?php

declare(strict_types=1);

namespace Modules\Department\Application\UseCases;

use Modules\Department\Domain\Ports\DepartmentRepositoryInterface;

final class GetDepartmentListUseCase
{
    public function __construct(
        private readonly DepartmentRepositoryInterface $repository
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function execute(array $filters = []): array
    {
        $departamentos = $this->repository->list($filters);
        $candidatos = $this->repository->getCandidatesForLeadership();

        return [
            'departamentos' => array_map(fn($d) => $d->toArray(), $departamentos),
            'candidatos' => $candidatos,
            'filters' => $filters,
        ];
    }
}

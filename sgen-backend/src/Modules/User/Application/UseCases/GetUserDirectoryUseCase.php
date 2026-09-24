<?php

declare(strict_types=1);

namespace Modules\User\Application\UseCases;

use Modules\User\Application\DTOs\UserKpisDTO;
use Modules\User\Application\Mappers\UserListItemMapper;
use Modules\User\Domain\Ports\UserRepositoryInterface;

final readonly class GetUserDirectoryUseCase
{
    public function __construct(
        private UserRepositoryInterface $repository
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function execute(array $filters = []): array
    {
        $rawRows = $this->repository->listWithDetails($filters);
        $kpisData = $this->repository->getKpis();

        $users = array_map(
            fn (object|array $row): array => UserListItemMapper::fromRow($row)->toArray(),
            $rawRows
        );

        $kpis = new UserKpisDTO(
            totalUsers: $kpisData['total_users'],
            adminsCount: $kpisData['admins_count'],
            techsCount: $kpisData['techs_count']
        );

        return [
            'users' => $users,
            'kpis' => $kpis->toArray(),
            'filters' => $filters,
            'departamentos' => $this->repository->getDepartmentsLookup(),
            'empleados' => $this->repository->getEmployeesLookup(),
        ];
    }
}

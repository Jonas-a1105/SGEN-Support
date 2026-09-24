<?php

declare(strict_types=1);

namespace Modules\Department\Application\UseCases;

use Modules\Department\Application\DTOs\DepartmentDetailDTO;
use Modules\Department\Domain\Ports\DepartmentRepositoryInterface;

final class GetDepartmentDetailUseCase
{
    public function __construct(
        private readonly DepartmentRepositoryInterface $repository
    ) {
    }

    public function execute(int $id): ?DepartmentDetailDTO
    {
        return $this->repository->findById($id);
    }
}

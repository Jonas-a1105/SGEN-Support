<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Modules\Support\Application\DTOs\TicketDetailDTO;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class GetTicketDetailUseCase
{
    public function __construct(
        private readonly SupportRepositoryInterface $repository
    ) {
    }

    /**
     * @return array{
     *     detail: array<string, mixed>,
     *     options: array<string, mixed>
     * }|null
     */
    public function execute(int $id): ?array
    {
        $dto = $this->repository->findById($id);
        if ($dto === null) {
            return null;
        }

        $options = $this->repository->getFormOptions();

        return array_merge($dto->toArray(), [
            'options' => $options,
        ]);
    }
}

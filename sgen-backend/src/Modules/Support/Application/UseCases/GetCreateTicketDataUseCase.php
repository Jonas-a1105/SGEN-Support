<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final readonly class GetCreateTicketDataUseCase
{
    public function __construct(
        private SupportRepositoryInterface $repository
    ) {}

    /**
     * @return array{options: array<string, mixed>}
     */
    public function execute(): array
    {
        return [
            'options' => $this->repository->getFormOptions(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class GenerateTicketPdfUseCase
{
    public function __construct(
        private SupportRepositoryInterface $repository
    ) {}

    public function execute(int $ticketId): string
    {
        return $this->repository->generateTicketPdf($ticketId);
    }
}

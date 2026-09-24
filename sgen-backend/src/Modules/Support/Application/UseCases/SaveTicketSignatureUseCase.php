<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class SaveTicketSignatureUseCase
{
    public function __construct(
        private SupportRepositoryInterface $repository
    ) {}

    public function execute(int $ticketId, string $signatureData): bool
    {
        return $this->repository->saveSignature($ticketId, $signatureData);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class UploadTicketAttachmentUseCase
{
    public function __construct(
        private SupportRepositoryInterface $repository
    ) {}

    public function execute(int $ticketId, string $filePath, string $originalName, string $mimeType, int $size, int $userId): int
    {
        return $this->repository->uploadAttachment($ticketId, $filePath, $originalName, $mimeType, $size, $userId);
    }
}

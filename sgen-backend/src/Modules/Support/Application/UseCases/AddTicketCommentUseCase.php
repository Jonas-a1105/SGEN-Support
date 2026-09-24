<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class AddTicketCommentUseCase
{
    public function __construct(
        private readonly SupportRepositoryInterface $repository
    ) {
    }

    public function execute(int $ticketId, int $userId, string $comment, bool $isInternal = false): bool
    {
        return $this->repository->addComment($ticketId, $userId, $comment, $isInternal);
    }
}

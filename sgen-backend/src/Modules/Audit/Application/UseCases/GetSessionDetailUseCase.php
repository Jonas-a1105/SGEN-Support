<?php

declare(strict_types=1);

namespace Modules\Audit\Application\UseCases;

use Modules\Audit\Application\DTOs\AuditDetailDTO;
use Modules\Audit\Application\Mappers\AuditListItemMapper;
use Modules\Audit\Domain\Ports\AuditRepositoryInterface;

final readonly class GetSessionDetailUseCase
{
    public function __construct(
        private AuditRepositoryInterface $repository
    ) {}

    public function execute(int $id): ?AuditDetailDTO
    {
        $raw = $this->repository->findSessionById($id);
        if ($raw === null) {
            return null;
        }

        $item = AuditListItemMapper::fromRow($raw);

        return new AuditDetailDTO(
            id: $item->id,
            userId: $item->userId,
            username: $item->username,
            avatarInitials: $item->avatarInitials,
            startTime: $item->startTime,
            endTime: $item->endTime,
            duration: $item->duration,
            status: $item->status,
            isActive: $item->isActive
        );
    }
}

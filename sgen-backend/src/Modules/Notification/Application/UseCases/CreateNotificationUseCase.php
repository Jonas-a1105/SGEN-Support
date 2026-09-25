<?php

declare(strict_types=1);

namespace Modules\Notification\Application\UseCases;

use Modules\Notification\Domain\Enums\NotificationType;
use Modules\Notification\Domain\Models\Notification;
use Modules\Notification\Domain\Ports\NotificationRepositoryInterface;

final class CreateNotificationUseCase
{
    public function __construct(
        private NotificationRepositoryInterface $repository
    ) {}

    public function execute(
        int $userId,
        NotificationType $type,
        string $title,
        string $message,
        ?string $link = null
    ): int {
        $notification = Notification::create($userId, $type, $title, $message, $link);
        return $this->repository->save($notification);
    }
}

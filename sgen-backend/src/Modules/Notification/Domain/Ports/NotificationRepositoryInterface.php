<?php

declare(strict_types=1);

namespace Modules\Notification\Domain\Ports;

use Modules\Notification\Domain\Models\Notification;

interface NotificationRepositoryInterface
{
    public function save(Notification $notification): int;
    public function findByUser(int $userId, int $limit = 20): array;
    public function findUnreadByUser(int $userId): array;
    public function markAsRead(int $notificationId): bool;
    public function markAllAsRead(int $userId): bool;
    public function countUnread(int $userId): int;
    public function delete(int $notificationId): bool;
}

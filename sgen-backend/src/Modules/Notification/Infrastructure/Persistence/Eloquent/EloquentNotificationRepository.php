<?php

declare(strict_types=1);

namespace Modules\Notification\Infrastructure\Persistence\Eloquent;

use Illuminate\Support\Facades\DB;
use Modules\Notification\Domain\Models\Notification;
use Modules\Notification\Domain\Ports\NotificationRepositoryInterface;

final class EloquentNotificationRepository implements NotificationRepositoryInterface
{
    public function save(Notification $notification): int
    {
        return (int) DB::table('notificaciones')->insertGetId([
            'usuario_id' => $notification->userId(),
            'tipo' => $notification->type()->value,
            'titulo' => $notification->title(),
            'mensaje' => $notification->message(),
            'enlace' => $notification->link(),
            'leido' => $notification->isRead(),
            'created_at' => $notification->createdAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    public function findByUser(int $userId, int $limit = 20): array
    {
        return DB::table('notificaciones')
            ->where('usuario_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->all();
    }

    public function findUnreadByUser(int $userId): array
    {
        return DB::table('notificaciones')
            ->where('usuario_id', $userId)
            ->where('leido', false)
            ->orderByDesc('created_at')
            ->get()
            ->all();
    }

    public function markAsRead(int $notificationId): bool
    {
        return DB::table('notificaciones')
            ->where('id', $notificationId)
            ->update(['leido' => true, 'read_at' => now()]) > 0;
    }

    public function markAllAsRead(int $userId): bool
    {
        return DB::table('notificaciones')
            ->where('usuario_id', $userId)
            ->where('leido', false)
            ->update(['leido' => true, 'read_at' => now()]) > 0;
    }

    public function countUnread(int $userId): int
    {
        return (int) DB::table('notificaciones')
            ->where('usuario_id', $userId)
            ->where('leido', false)
            ->count();
    }

    public function delete(int $notificationId): bool
    {
        return DB::table('notificaciones')
            ->where('id', $notificationId)
            ->delete() > 0;
    }
}

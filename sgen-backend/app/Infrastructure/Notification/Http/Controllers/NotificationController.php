<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Notification\Application\UseCases\CreateNotificationUseCase;
use Modules\Notification\Domain\Ports\NotificationRepositoryInterface;

final class NotificationController extends Controller
{
    public function __construct(
        private NotificationRepositoryInterface $repository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()?->id ?? 1;
        $notifications = $this->repository->findByUser($userId);

        return response()->json($notifications);
    }

    public function unread(Request $request): JsonResponse
    {
        $userId = $request->user()?->id ?? 1;
        $unread = $this->repository->findUnreadByUser($userId);

        return response()->json($unread);
    }

    public function markAsRead(int $id, Request $request): JsonResponse
    {
        $this->repository->markAsRead($id);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $userId = $request->user()?->id ?? 1;
        $this->repository->markAllAsRead($userId);

        return response()->json(['success' => true]);
    }

    public function countUnread(Request $request): JsonResponse
    {
        $userId = $request->user()?->id ?? 1;
        $count = $this->repository->countUnread($userId);

        return response()->json(['count' => $count]);
    }

    public function create(Request $request, CreateNotificationUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'link' => 'nullable|string|max:500',
        ]);

        $notificationId = $useCase->execute(
            (int) $validated['user_id'],
            NotificationType::from($validated['type']),
            $validated['title'],
            $validated['message'],
            $validated['link'] ?? null
        );

        return response()->json(['id' => $notificationId], 201);
    }

    public function delete(int $id): JsonResponse
    {
        $this->repository->delete($id);

        return response()->json(['success' => true]);
    }
}

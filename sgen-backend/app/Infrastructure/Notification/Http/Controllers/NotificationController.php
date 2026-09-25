<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Notification\Application\UseCases\CreateNotificationUseCase;
use Modules\Notification\Domain\Enums\NotificationType;
use Modules\Notification\Domain\Ports\NotificationRepositoryInterface;

final class NotificationController extends Controller
{
    public function __construct(
        private NotificationRepositoryInterface $repository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $notifications = $this->repository->findByUser((int) $request->user()->id);

        return response()->json($notifications);
    }

    public function unread(Request $request): JsonResponse
    {
        $unread = $this->repository->findUnreadByUser((int) $request->user()->id);

        return response()->json($unread);
    }

    public function markAsRead(int $id, Request $request): JsonResponse
    {
        // Alcance obligatorio: solo el dueño puede marcar su notificación.
        $this->repository->markAsRead($id, (int) $request->user()->id);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->repository->markAllAsRead((int) $request->user()->id);

        return response()->json(['success' => true]);
    }

    public function countUnread(Request $request): JsonResponse
    {
        $count = $this->repository->countUnread((int) $request->user()->id);

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

    public function delete(int $id, Request $request): JsonResponse
    {
        // Alcance obligatorio: solo el dueño puede eliminar su notificación.
        $this->repository->delete($id, (int) $request->user()->id);

        return response()->json(['success' => true]);
    }
}

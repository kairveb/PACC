<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class NotificationApiController extends Controller
{
    /**
     * List the current user's notifications.
     */
    public function index(): JsonResponse
    {
        $notifications = auth()->user()->notifications()->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Notifications retrieved successfully.',
            'data' => $notifications->items(),
            'meta' => ['current_page' => $notifications->currentPage(), 'last_page' => $notifications->lastPage(), 'total' => $notifications->total()],
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markRead(string $id): JsonResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }
}

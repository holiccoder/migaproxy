<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class UserNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $notifications = $user->notifications()
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }

    public function markAsRead(Request $request, string $notification): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $databaseNotification = $user->notifications()
            ->where('id', $notification)
            ->first();

        if (! $databaseNotification instanceof DatabaseNotification) {
            return response()->json([
                'message' => 'Notification not found.',
            ], 404);
        }

        if (! $databaseNotification->read_at) {
            $databaseNotification->markAsRead();
        }

        return response()->json([
            'message' => 'Notification marked as read.',
        ]);
    }
}

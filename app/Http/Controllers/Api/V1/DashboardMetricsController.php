<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardMetricsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $activeSubscriptions = $user->subscriptions()
            ->active()
            ->count();

        $paidOrders = $user->orders()
            ->where('status', Order::STATUS_PAID)
            ->count();

        $openTickets = $user->tickets()
            ->whereIn('status', [Ticket::STATUS_OPEN, Ticket::STATUS_IN_PROGRESS])
            ->count();

        $unreadNotifications = $user->unreadNotifications()
            ->count();

        return response()->json([
            'data' => [
                'active_subscriptions' => $activeSubscriptions,
                'paid_orders' => $paidOrders,
                'open_tickets' => $openTickets,
                'unread_notifications' => $unreadNotifications,
            ],
        ]);
    }
}

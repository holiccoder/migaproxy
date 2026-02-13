<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\OrderIndexRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function index(OrderIndexRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $filters = $request->validated();

        $orders = $user->orders()
            ->with('plan:id,name')
            ->when($filters['status'] ?? null, function ($query, string $status): void {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }
}

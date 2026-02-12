<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AffiliateController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $affiliate = Affiliate::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $affiliate) {
            return response()->json([
                'message' => 'Affiliate account not found.',
            ], 404);
        }

        $clicksCount = $affiliate->clicks()->count();
        $conversionsCount = $affiliate->conversions()->count();
        $approvedCommission = $affiliate->conversions()
            ->whereIn('status', ['approved', 'paid'])
            ->sum('commission_amount');
        $pendingCommission = $affiliate->conversions()
            ->where('status', 'pending')
            ->sum('commission_amount');

        return response()->json([
            'data' => [
                'affiliate' => $affiliate,
                'stats' => [
                    'clicks_count' => $clicksCount,
                    'conversions_count' => $conversionsCount,
                    'approved_commission' => (int) $approvedCommission,
                    'pending_commission' => (int) $pendingCommission,
                    'total_earnings' => (int) $affiliate->total_earnings,
                ],
            ],
        ]);
    }

    public function conversions(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $affiliate = Affiliate::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $affiliate) {
            return response()->json([
                'message' => 'Affiliate account not found.',
            ], 404);
        }

        $conversions = $affiliate->conversions()
            ->with(['order', 'user'])
            ->latest()
            ->paginate(20);

        return response()->json($conversions);
    }
}

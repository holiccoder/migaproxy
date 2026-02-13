<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateConversion;
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
        $availableToWithdraw = $affiliate->conversions()
            ->where('status', 'approved')
            ->sum('commission_amount');
        $paidOutCommission = $affiliate->conversions()
            ->where('status', 'paid')
            ->sum('commission_amount');
        $conversionRate = $clicksCount > 0
            ? round(($conversionsCount / $clicksCount) * 100, 2)
            : 0.0;
        $minimumThreshold = 5000;

        $trafficSources = $affiliate->clicks()
            ->selectRaw("COALESCE(NULLIF(referrer, ''), 'Direct') as source, COUNT(*) as clicks_count")
            ->groupBy('source')
            ->orderByDesc('clicks_count')
            ->limit(10)
            ->get()
            ->map(function (object $row): array {
                $source = (string) ($row->source ?? 'Direct');
                $host = parse_url($source, PHP_URL_HOST);

                return [
                    'source' => is_string($host) && $host !== '' ? $host : $source,
                    'clicks_count' => (int) ($row->clicks_count ?? 0),
                ];
            })
            ->values();

        $payoutHistory = $affiliate->conversions()
            ->with(['order.plan:id,name'])
            ->where('status', AffiliateConversion::STATUS_PAID)
            ->latest('paid_at')
            ->limit(20)
            ->get()
            ->map(function (AffiliateConversion $conversion): array {
                return [
                    'id' => $conversion->id,
                    'date' => optional($conversion->paid_at)->toIso8601String(),
                    'plan_name' => $conversion->order?->plan?->name,
                    'commission_amount' => (int) $conversion->commission_amount,
                    'status' => $conversion->status,
                ];
            })
            ->values();

        return response()->json([
            'data' => [
                'affiliate' => $affiliate,
                'stats' => [
                    'clicks_count' => $clicksCount,
                    'conversions_count' => $conversionsCount,
                    'conversion_rate' => $conversionRate,
                    'approved_commission' => (int) $approvedCommission,
                    'pending_commission' => (int) $pendingCommission,
                    'available_to_withdraw' => (int) $availableToWithdraw,
                    'paid_out_commission' => (int) $paidOutCommission,
                    'minimum_threshold' => $minimumThreshold,
                    'total_earnings' => (int) $affiliate->total_earnings,
                ],
                'traffic_sources' => $trafficSources,
                'payout_history' => $payoutHistory,
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
            ->with([
                'order',
                'order.plan:id,name',
                'user:id,email',
            ])
            ->latest()
            ->paginate(20);

        return response()->json($conversions);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Affiliates\AffiliateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class AffiliateTrackingController extends Controller
{
    public function track(Request $request, AffiliateService $affiliateService): JsonResponse
    {
        $payload = $request->validate([
            'code' => ['required', 'string', 'max:64'],
        ]);

        try {
            $click = $affiliateService->trackClick($payload['code'], $request);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Affiliate click tracked.',
            'data' => [
                'affiliate_code' => $click->code,
                'affiliate_id' => $click->affiliate_id,
            ],
        ], 201);
    }
}

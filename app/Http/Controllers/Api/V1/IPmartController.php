<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Api\IPmart\DataRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IPmartController extends Controller
{
    /**
     * Get available countries from IPmart
     */
    public function getCountries(): JsonResponse
    {
        $result = DataRequest::chooseArea(0);

        if ($result) {
            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch countries from IPmart',
        ], 500);
    }

    /**
     * Get states/provinces for a country
     */
    public function getStates(Request $request): JsonResponse
    {
        $request->validate([
            'country_code' => 'required|string|size:2',
        ]);

        $result = DataRequest::chooseArea(1, $request->country_code);

        if ($result) {
            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch states from IPmart',
        ], 500);
    }

    /**
     * Get cities for a state
     */
    public function getCities(Request $request): JsonResponse
    {
        $request->validate([
            'country_code' => 'required|string|size:2',
            'state' => 'required|string',
        ]);

        $result = DataRequest::chooseArea(2, $request->country_code, $request->state);

        if ($result) {
            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch cities from IPmart',
        ], 500);
    }

    /**
     * Get available proxy protocols
     */
    public function getProtocols(): JsonResponse
    {
        $instance = new DataRequest;
        $result = $instance->chooseProtocol();

        if ($result) {
            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch protocols from IPmart',
        ], 500);
    }

    /**
     * Get available proxy patterns
     */
    public function getPatterns(): JsonResponse
    {
        $instance = new DataRequest;
        $result = $instance->proxyPattern();

        if ($result) {
            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch patterns from IPmart',
        ], 500);
    }

    /**
     * Get proxy rules
     */
    public function getRules(): JsonResponse
    {
        $result = DataRequest::proxyRules();

        if ($result) {
            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch rules from IPmart',
        ], 500);
    }

    /**
     * Get static residential products
     */
    public function getStaticProducts(): JsonResponse
    {
        $result = DataRequest::getStaticResidentialProductList();

        if ($result) {
            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch static products from IPmart',
        ], 500);
    }

    /**
     * Get static IP count by country code
     */
    public function getStaticIpCount(Request $request): JsonResponse
    {
        $request->validate([
            'country_code' => 'required|string',
        ]);

        $result = DataRequest::getStaticIpCountByCode($request->country_code);

        if ($result) {
            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch IP count from IPmart',
        ], 500);
    }

    /**
     * Get traffic usage history
     */
    public function getTrafficHistory(Request $request): JsonResponse
    {
        $request->validate([
            'ipmart_id' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $instance = new DataRequest;
        $result = $instance->getTrafficUsageHistory(
            $request->ipmart_id,
            $request->start_date,
            $request->end_date
        );

        if ($result) {
            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch traffic history from IPmart',
        ], 500);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ChangeProxyPasswordRequest;
use App\Http\Requests\Api\V1\GetProxyApiLinkRequest;
use App\Http\Requests\Api\V1\GetProxyCitiesRequest;
use App\Http\Requests\Api\V1\GetProxyStatesRequest;
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
    public function getStates(GetProxyStatesRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = DataRequest::chooseArea(1, $validated['country_code']);

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
    public function getCities(GetProxyCitiesRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = DataRequest::chooseArea(2, $validated['country_code'], $validated['state']);

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
     * Get proxy options
     */
    public function getProxyOptions(): JsonResponse
    {
        $instance = new DataRequest;

        $countries = $instance->getCountries();
        $protocols = $instance->chooseProtocol();
        $patterns = $instance->proxyPattern();
        $rules = $instance->getProxyRules();

        if ($countries && $protocols && $patterns && $rules) {
            return response()->json([
                'success' => true,
                'data' => [
                    'countries' => $countries,
                    'protocols' => $protocols,
                    'patterns' => $patterns,
                    'rules' => $rules,
                    'test_link' => '',
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch proxy options from IPmart',
        ], 500);
    }

    public function changeProxyPassword(ChangeProxyPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $ipmartAccount = $request->user()
            ->ipmart()
            ->where('ipmart_id', $validated['ipmart_id'])
            ->first();

        if (! $ipmartAccount) {
            return response()->json([
                'success' => false,
                'message' => 'IPmart account not found for this user.',
            ], 404);
        }

        $result = DataRequest::changeProxyPassword($validated['ipmart_id'], $validated['proxyPwd']);

        if (! $result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change proxy password on IPmart.',
            ], 500);
        }

        $ipmartAccount->update([
            'proxyPwd' => $validated['proxyPwd'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Proxy password changed successfully.',
            'data' => [
                'ipmart_id' => $ipmartAccount->ipmart_id,
                'proxyPwd' => $ipmartAccount->proxyPwd,
            ],
        ]);
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

    /**
     * Get proxy API link for rotating residential proxies
     */
    public function getProxyAPILink(GetProxyApiLinkRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = DataRequest::generateAPILink(
            $validated['apiCntryCode'] ?? 'CA',
            $validated['subUserId'],
            $validated['cntryCode'],
            $validated['time'],
            $validated['num'],
            $validated['format'],
            $validated['stateName'] ?? null,
            $validated['cityName'] ?? null,
        );

        if ($result) {
            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to generate proxy API link from IPmart',
        ], 500);

    }
}

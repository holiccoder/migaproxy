<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ChangeProxyPasswordRequest;
use App\Http\Requests\Api\V1\GenerateTestLinkRequest;
use App\Http\Requests\Api\V1\GetProxyApiLinkRequest;
use App\Http\Requests\Api\V1\GetProxyCitiesRequest;
use App\Http\Requests\Api\V1\GetProxyStatesRequest;
use App\Http\Requests\Api\V1\PayForCustomerUsingBalanceRequest;
use App\Models\TrafficHistory;
use App\Models\User;
use App\Services\Api\IPmart\DataRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IPmartController extends Controller
{
    private const IPMART_PROXY_HOST = 'proxy.ipmart.io';

    private const MIGAPROXY_PROXY_HOST = 'proxy.migaproxy.com';

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

    public function getUserInfo(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        if (! $authenticatedUser instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $ipmartAccount = $authenticatedUser->ipmart;

        if (! $ipmartAccount) {
            return response()->json([
                'success' => false,
                'message' => 'IPmart account not found for this user.',
            ], 404);
        }

        $result = DataRequest::getUserInfo($ipmartAccount->ipmart_id);

        if (! $result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user info from IPmart.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    private function replaceProxyHostForGenerateTestLink(mixed $payload): mixed
    {
        if (is_array($payload)) {
            foreach ($payload as $key => $value) {
                $payload[$key] = $this->replaceProxyHostForGenerateTestLink($value);
            }

            return $payload;
        }

        if (is_string($payload)) {
            return str_replace(self::IPMART_PROXY_HOST, self::MIGAPROXY_PROXY_HOST, $payload);
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array<string, mixed>
     */
    private function normalizeProxyApiLinkResponse(array $response): array
    {
        $ips = $response['ips'] ?? null;

        if (! is_array($ips)) {
            return $response;
        }

        $normalizedIps = [];

        foreach ($ips as $proxyEntry) {
            if (! is_string($proxyEntry)) {
                continue;
            }

            $normalizedEntry = $this->extractProxyEndpoint($proxyEntry);

            if ($normalizedEntry !== null) {
                $normalizedIps[] = $normalizedEntry;
            }
        }

        $response['ips'] = array_values(array_unique($normalizedIps));

        return $response;
    }

    private function extractProxyEndpoint(string $proxyEntry): ?string
    {
        $parts = explode(':', $proxyEntry);

        if (count($parts) < 2) {
            return null;
        }

        $host = trim($parts[0]);
        $port = trim($parts[1]);

        if ($host === '' || ! ctype_digit($port)) {
            return null;
        }

        $portNumber = (int) $port;

        if ($portNumber < 1 || $portNumber > 65535) {
            return null;
        }

        return $host.':'.$portNumber;
    }

    public function getProxyInfo(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        if (! $authenticatedUser instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $ipmartAccount = $authenticatedUser->ipmart;

        if (! $ipmartAccount) {
            return response()->json([
                'success' => false,
                'message' => 'IPmart account not found for this user.',
            ], 404);
        }

        $instance = new DataRequest;
        $result = $instance->getAvailableTraffic($ipmartAccount->ipmart_id);

        if (! $result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch proxy info from IPmart.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    public function generateTestLink(GenerateTestLinkRequest $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        if (! $authenticatedUser instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $ipmartAccount = $authenticatedUser->ipmart;

        if (! $ipmartAccount) {
            return response()->json([
                'success' => false,
                'message' => 'IPmart account not found for this user.',
            ], 404);
        }

        $validated = $request->validated();

        $result = DataRequest::generateProxyLinks(
            $ipmartAccount->ipmart_id,
            (int) ($validated['protocol'] ?? 0),
            (int) ($validated['pattern'] ?? 1),
            (int) ($validated['rule'] ?? 1),
            (int) ($validated['count'] ?? 0),
            $validated['country'] ?? null,
            $validated['state'] ?? null,
            $validated['city'] ?? null,
        );

        if (! $result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate test link from IPmart.',
            ], 500);
        }

        $result = $this->replaceProxyHostForGenerateTestLink($result);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    public function payForCustomerUsingBalance(PayForCustomerUsingBalanceRequest $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        if (! $authenticatedUser instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $ipmartAccount = $authenticatedUser->ipmart;

        if (! $ipmartAccount) {
            return response()->json([
                'success' => false,
                'message' => 'IPmart account not found for this user.',
            ], 404);
        }

        $validated = $request->validated();
        $amount = (int) $validated['amount'];

        $result = DataRequest::payForCustomerUsingBalance($ipmartAccount->ipmart_id, $amount);

        if (! $result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to place IPmart order for customer.',
            ], 500);
        }

        if (is_array($result) && array_key_exists('plan_balance', $result)) {
            $ipmartAccount->update([
                'plan_balance' => (int) $result['plan_balance'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Customer order placed successfully.',
            'data' => [
                'user_id' => $authenticatedUser->id,
                'ipmart_id' => $ipmartAccount->ipmart_id,
                'amount' => $amount,
                'order' => $result,
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

    public function getTrafficHistoryByUserId(Request $request, int $userId): JsonResponse
    {
        $authenticatedUser = $request->user();

        if (! $authenticatedUser instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($authenticatedUser->id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to access this traffic history.',
            ], 403);
        }

        $ipmartAccount = $authenticatedUser->ipmart;

        if (! $ipmartAccount) {
            return response()->json([
                'success' => false,
                'message' => 'IPmart account not found for this user.',
            ], 404);
        }

        $trafficHistories = TrafficHistory::query()
            ->where('ipmart_id', $ipmartAccount->ipmart_id)
            ->latest('request_date')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $trafficHistories,
        ]);
    }

    public function orderForCustomer(int $userId, int $amount): JsonResponse
    {
        if ($amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Amount must be greater than zero.',
            ], 422);
        }

        $user = User::query()
            ->with('ipmart')
            ->find($userId);

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $ipmartAccount = $user->ipmart;

        if (! $ipmartAccount) {
            return response()->json([
                'success' => false,
                'message' => 'IPmart account not found for this user.',
            ], 404);
        }

        $result = DataRequest::payForCustomerUsingBalance($ipmartAccount->ipmart_id, $amount);

        if (! $result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to place IPmart order for customer.',
            ], 500);
        }

        if (is_array($result) && array_key_exists('plan_balance', $result)) {
            $ipmartAccount->update([
                'plan_balance' => (int) $result['plan_balance'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Customer order placed successfully.',
            'data' => [
                'user_id' => $user->id,
                'ipmart_id' => $ipmartAccount->ipmart_id,
                'amount' => $amount,
                'order' => $result,
            ],
        ]);
    }

    /**
     * Get proxy API link for rotating residential proxies
     */
    public function getProxyAPILink(GetProxyApiLinkRequest $request): JsonResponse|string
    {
        $authenticatedUser = $request->user();

        if (! $authenticatedUser instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $ipmartAccount = $authenticatedUser->ipmart;

        if (! $ipmartAccount) {
            return response()->json([
                'success' => false,
                'message' => 'IPmart account not found for this user.',
            ], 404);
        }

        $validated = $request->validated();

        $result = DataRequest::generateAPILink(
            strtoupper((string) ($validated['apiCntryCode'] ?? 'CA')),
            $ipmartAccount->ipmart_id,
            strtoupper((string) $validated['cntryCode']),
            (int) $validated['time'],
            (int) $validated['num'],
            (int) $validated['format'],
            $validated['stateName'] ?? null,
            $validated['cityName'] ?? null,
        );

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate proxy API link from IPmart.',
            ], 500);
        }

        if (! is_array($result)) {
            return $result;
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}

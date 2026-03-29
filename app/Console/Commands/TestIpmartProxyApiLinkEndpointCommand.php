<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TestIpmartProxyApiLinkEndpointCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ipmart:test-proxy-api-link-endpoint
        {user_id : User ID from users table}
        {--cntry-code=US : Two-letter country code}
        {--time=5 : Session time in minutes}
        {--num=1 : Number of IPs}
        {--format=1 : Response format (1 or 2)}
        {--api-cntry-code=CA : Optional API country code}
        {--state-name= : Optional state/province name}
        {--city-name= : Optional city name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the authenticated IPmart proxy-api-link API endpoint response.';

    /**
     * Execute the console command.
     */
    public function handle(Kernel $kernel): int
    {
        $userId = (int) $this->argument('user_id');

        if ($userId <= 0) {
            $this->error('The user_id argument must be a positive integer.');

            return self::INVALID;
        }

        $cntryCode = strtoupper(trim((string) $this->option('cntry-code')));
        $apiCntryCode = strtoupper(trim((string) $this->option('api-cntry-code')));
        $stateName = trim((string) $this->option('state-name'));
        $cityName = trim((string) $this->option('city-name'));
        $format = (int) $this->option('format');
        $time = (int) $this->option('time');
        $num = (int) $this->option('num');

        if (strlen($cntryCode) !== 2) {
            $this->error('The --cntry-code option must be exactly 2 characters.');

            return self::INVALID;
        }

        if ($apiCntryCode !== '' && strlen($apiCntryCode) !== 2) {
            $this->error('The --api-cntry-code option must be exactly 2 characters when provided.');

            return self::INVALID;
        }

        if ($time <= 0) {
            $this->error('The --time option must be a positive integer.');

            return self::INVALID;
        }

        if ($num <= 0) {
            $this->error('The --num option must be a positive integer.');

            return self::INVALID;
        }

        if (! in_array($format, [1, 2], true)) {
            $this->error('The --format option must be either 1 or 2.');

            return self::INVALID;
        }

        $user = User::query()->with('ipmart')->find($userId);

        if (! $user instanceof User) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        if ($user->ipmart === null) {
            $this->error('IPmart account not found for this user.');

            return self::FAILURE;
        }

        if (! $user->hasVerifiedEmail()) {
            $this->error('User email is not verified. This endpoint requires a verified user.');

            return self::FAILURE;
        }

        $this->line('Testing user_id='.$user->id.' with ipmart_id='.$user->ipmart->ipmart_id);

        $token = $user->createToken('ipmart-proxy-api-link-check');

        $query = [
            'cntryCode' => $cntryCode,
            'time' => $time,
            'num' => $num,
            'format' => $format,
        ];

        if ($apiCntryCode !== '') {
            $query['apiCntryCode'] = $apiCntryCode;
        }

        if ($stateName !== '') {
            $query['stateName'] = $stateName;
        }

        if ($cityName !== '') {
            $query['cityName'] = $cityName;
        }

        $request = Request::create('/api/v1/ipmart/proxy-api-link', 'POST', $query);
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('Authorization', 'Bearer '.$token->plainTextToken);

        $response = null;

        try {
            $response = $kernel->handle($request);
        } finally {
            if ($response instanceof Response) {
                $kernel->terminate($request, $response);
            }

            $token->accessToken->delete();
        }

        if ($format === 1) {
            $content = $response->getContent();

            if ($response->getStatusCode() >= 400) {
                $this->error('Request failed with HTTP status: '.$response->getStatusCode());
                $this->line($content);

                return self::FAILURE;
            }

            if (! $this->isValidIpPort(trim($content))) {
                $this->error('Response is not a valid ip:port format.');
                $this->line('Raw response: '.$content);

                return self::FAILURE;
            }

            $this->info('IPmart proxy-api-link endpoint responded successfully (format=1).');
            $this->line('Proxy endpoint: '.$content);

            return self::SUCCESS;
        }

        $payload = $this->decodePayload($response);
        $message = (string) ($payload['message'] ?? 'No message returned.');

        if ($response->getStatusCode() >= 400 || ! (bool) ($payload['success'] ?? false)) {
            $this->error($message);
            $this->line('HTTP status: '.$response->getStatusCode());
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::FAILURE;
        }

        $invalidProxyEntries = $this->collectInvalidProxyEntries($payload);

        if ($invalidProxyEntries !== []) {
            $this->error('Some proxy entries are not in ip:port format.');
            $this->line(json_encode([
                'invalid_ips' => $invalidProxyEntries,
                'data' => $payload['data'] ?? [],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::FAILURE;
        }

        $this->info('IPmart proxy-api-link endpoint responded successfully (format=2).');
        $this->info('All proxy entries are valid ip:port values.');
        $this->line(json_encode($payload['data'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    private function collectInvalidProxyEntries(array $payload): array
    {
        $data = $payload['data'] ?? null;

        if (! is_array($data)) {
            return [];
        }

        $ips = $data['ips'] ?? null;

        if (! is_array($ips)) {
            return [];
        }

        $invalidEntries = [];

        foreach ($ips as $proxyEntry) {
            if (! is_string($proxyEntry) || ! $this->isValidIpPort($proxyEntry)) {
                $serializedEntry = json_encode($proxyEntry, JSON_UNESCAPED_SLASHES);

                $invalidEntries[] = is_scalar($proxyEntry)
                    ? (string) $proxyEntry
                    : ($serializedEntry === false ? 'unserializable-proxy-entry' : $serializedEntry);
            }
        }

        return $invalidEntries;
    }

    private function isValidIpPort(string $proxyEntry): bool
    {
        if (! preg_match('/^(\d{1,3}\.){3}\d{1,3}:\d{1,5}$/', $proxyEntry)) {
            return false;
        }

        [$ipAddress, $port] = explode(':', $proxyEntry, 2);

        foreach (explode('.', $ipAddress) as $octet) {
            $octetValue = (int) $octet;

            if ($octetValue < 0 || $octetValue > 255) {
                return false;
            }
        }

        $portNumber = (int) $port;

        return $portNumber >= 1 && $portNumber <= 65535;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodePayload(Response $response): array
    {
        $content = $response->getContent();
        $decoded = json_decode((string) $content, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        return [
            'message' => 'Endpoint did not return valid JSON.',
            'raw_response' => $content,
        ];
    }
}

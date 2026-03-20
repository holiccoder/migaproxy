<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TestIpmartGenerateTestLinkEndpointCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ipmart:test-generate-test-link-endpoint
        {user_id : User ID from users table}
        {--protocol=0 : Protocol id (0 http, 2 socks5)}
        {--pattern=1 : Pattern id}
        {--rule=1 : Rule id}
        {--count=0 : Number of IPs}
        {--country= : Optional country code}
        {--state= : Optional state name}
        {--city= : Optional city name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the authenticated IPmart generate-test-link API endpoint response.';

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

        $user = User::query()->find($userId);

        if (! $user instanceof User) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        if (! $user->hasVerifiedEmail()) {
            $this->error('User email is not verified. This endpoint requires a verified user.');

            return self::FAILURE;
        }

        $token = $user->createToken('ipmart-generate-test-link-check');

        $query = [
            'protocol' => (int) $this->option('protocol'),
            'pattern' => (int) $this->option('pattern'),
            'rule' => (int) $this->option('rule'),
            'count' => (int) $this->option('count'),
        ];

        foreach (['country', 'state', 'city'] as $optionalKey) {
            $value = $this->option($optionalKey);

            if (is_string($value) && $value !== '') {
                $query[$optionalKey] = $value;
            }
        }

        $request = Request::create('/api/v1/ipmart/generate-test-link?'.http_build_query($query), 'GET');
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

        $payload = $this->decodePayload($response);
        $message = (string) ($payload['message'] ?? 'No message returned.');

        if ($response->getStatusCode() >= 400 || ! (bool) ($payload['success'] ?? false)) {
            $this->error($message);
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::FAILURE;
        }

        $this->info('IPmart generate-test-link endpoint responded successfully.');
        $this->line(json_encode($payload['data'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
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

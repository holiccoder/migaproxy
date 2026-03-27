<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIpmartUserInfoEndpointCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ipmart:check-user-info {user_id : User ID from users table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the authenticated IPmart user info API endpoint response.';

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

        $token = $user->createToken('ipmart-user-info-check');

        $request = Request::create('/api/v1/ipmart/user-info', 'GET');
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

        $this->info('IPmart user info endpoint responded successfully.');
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

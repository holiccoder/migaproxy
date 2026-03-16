<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\V1\IPmartController;
use Illuminate\Console\Command;

class TestOrderForCustomerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ipmart:order-for-customer {user_id : User ID from users table} {amount : Capacity amount to order}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Place an IPmart order for a specific customer using plan balance.';

    /**
     * Execute the console command.
     */
    public function handle(IPmartController $ipmartController): int
    {
        $userId = (int) $this->argument('user_id');
        $amount = (int) $this->argument('amount');

        if ($userId <= 0) {
            $this->error('The user_id argument must be a positive integer.');

            return self::INVALID;
        }

        if ($amount <= 0) {
            $this->error('The amount argument must be a positive integer.');

            return self::INVALID;
        }

        $response = $ipmartController->orderForCustomer($userId, $amount);
        $payload = $response->getData(true);
        $message = is_array($payload) ? ($payload['message'] ?? 'No message returned.') : 'No message returned.';

        if ($response->getStatusCode() >= 400 || ! is_array($payload) || ! ($payload['success'] ?? false)) {
            $this->error((string) $message);

            return self::FAILURE;
        }

        $this->info((string) $message);
        $this->line(json_encode($payload['data'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }
}

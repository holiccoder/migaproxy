<?php

use App\Models\Admin;
use App\Models\Affiliate;
use App\Models\AffiliateClick;
use App\Models\AffiliateConversion;
use App\Models\BalanceHistory;
use App\Models\Ipmart;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\Subscription;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TrafficHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

test('command truncates users, ipmarts, and related tables while preserving non-user polymorphic rows', function () {
    User::factory()->create();
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $post = Post::factory()->create();

    $affiliate = Affiliate::factory()->create([
        'user_id' => $user->id,
    ]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'affiliate_id' => $affiliate->id,
        'affiliate_code' => $affiliate->code,
    ]);

    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'order_id' => $order->id,
    ]);

    AffiliateClick::factory()->create([
        'affiliate_id' => $affiliate->id,
        'code' => $affiliate->code,
    ]);

    AffiliateConversion::factory()->create([
        'affiliate_id' => $affiliate->id,
        'user_id' => $user->id,
        'order_id' => $order->id,
        'subscription_id' => $subscription->id,
    ]);

    $ticket = Ticket::factory()->create([
        'user_id' => $user->id,
    ]);

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
    ]);

    BalanceHistory::factory()->create([
        'user_id' => $user->id,
    ]);

    PostComment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user->id,
    ]);

    $ipmart = Ipmart::query()->create([
        'user_id' => (string) $user->id,
        'ipmart_id' => 'ipmart-test-id',
        'available_traffic' => '0',
        'ipmart_email' => 'ipmart-user@test.com',
        'plan_balance' => '0',
        'proxyName' => 'proxy-name',
        'proxyPwd' => 'proxy-password',
        'login_name' => 'proxy-login-name',
        'passwd' => 'proxy-login-password',
    ]);

    TrafficHistory::query()->create([
        'success_count' => 1,
        'length' => '1.250000',
        'request_date' => now(),
        'total_requests' => 1,
        'ipmart_id' => $ipmart->ipmart_id,
        'provider' => TrafficHistory::PROVIDER_IPMART,
    ]);

    $user->createToken('user-token');

    DB::table('notifications')->insert([
        'id' => (string) Str::uuid(),
        'type' => 'Tests\\Notifications\\UserNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['message' => 'user notification'], JSON_THROW_ON_ERROR),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = Admin::factory()->create();

    DB::table('personal_access_tokens')->insert([
        'tokenable_type' => Admin::class,
        'tokenable_id' => $admin->id,
        'name' => 'admin-token',
        'token' => hash('sha256', 'admin-token-value'),
        'abilities' => json_encode(['*'], JSON_THROW_ON_ERROR),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('notifications')->insert([
        'id' => (string) Str::uuid(),
        'type' => 'Tests\\Notifications\\AdminNotification',
        'notifiable_type' => Admin::class,
        'notifiable_id' => $admin->id,
        'data' => json_encode(['message' => 'admin notification'], JSON_THROW_ON_ERROR),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->artisan('users:truncate --force')
        ->expectsOutput('Users, ipmarts, and related tables were cleared successfully.')
        ->assertSuccessful();

    expect(DB::table('users')->count())->toBe(0);
    expect(DB::table('ipmarts')->count())->toBe(0);
    expect(DB::table('traffic_histories')->count())->toBe(0);
    expect(DB::table('affiliates')->count())->toBe(0);
    expect(DB::table('affiliate_clicks')->count())->toBe(0);
    expect(DB::table('affiliate_conversions')->count())->toBe(0);
    expect(DB::table('orders')->count())->toBe(0);
    expect(DB::table('subscriptions')->count())->toBe(0);
    expect(DB::table('tickets')->count())->toBe(0);
    expect(DB::table('ticket_messages')->count())->toBe(0);
    expect(DB::table('post_comments')->count())->toBe(0);
    expect(DB::table('balance_histories')->count())->toBe(0);
    expect(DB::table('personal_access_tokens')->count())->toBe(1);
    expect(DB::table('notifications')->count())->toBe(1);
    expect(DB::table('admins')->count())->toBe(1);
});

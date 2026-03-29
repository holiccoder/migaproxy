<?php

namespace Database\Seeders;

use App\Models\Ipmart;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            AdminSeeder::class,
            CouponSeeder::class,
            UserSeeder::class,
            PlanSeeder::class,
            CmsPageSeeder::class,
        ]);

        $user = User::query()->firstWhere('email', 'test@example.com');

        if ($user !== null) {
            Ipmart::query()->firstOrCreate(
                ['user_id' => (string) $user->id],
                [
                    'ipmart_id' => '68e77560d247fca264c188ab',
                    'available_traffic' => 2,
                    'ipmart_email' => 'user222@email.com',
                    'plan_balance' => 0,
                    'proxyName' => '0Yd6Tl3Ov9Lj',
                    'proxyPwd' => '3Km1Jv2Lc1Ls2Nl6Wt',
                    'login_name' => '0Yd6Tl3Ov9Lj',
                    'passwd' => 'password183929922UGHGG',
                    'created_at' => '2022-01-01 00:00:00',
                    'updated_at' => '2022-01-01 00:00:00',
                ]
            );
        }
    }
}

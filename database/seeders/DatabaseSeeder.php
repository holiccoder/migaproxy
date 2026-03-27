<?php

namespace Database\Seeders;

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
        // User::factory(10)->create();

        User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
            ]
        );

        $this->call([
            AdminSeeder::class,
            CouponSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
            PostSeeder::class,
            TicketSeeder::class,
            HelpCenterSeeder::class,
            CmsPageSeeder::class,
        ]);
    }
}

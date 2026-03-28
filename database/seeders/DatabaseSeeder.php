<?php

namespace Database\Seeders;

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
        $this->call([UserSeeder::class]);

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

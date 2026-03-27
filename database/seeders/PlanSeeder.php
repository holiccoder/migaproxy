<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => '1000G',
                'traffic' => 1000,
                'description' => '5GB Plan, Suits All Kinds of Use Cases',
                'features' => [
                    'Valid' => '30 days',
                    'Countries' => 'US, JP, CA, ID, AU, VN, UK, FR, DE',
                    'Protocals' => 'Http(S) / Socks5',
                    'City Level' => 'Yes',
                ],
                'price' => 800,
                'days' => 30,
                'is_active' => true,
            ],
            [
                'name' => '400G',
                'traffic' => 400,
                'description' => '400GB Plan, Suits All Kinds of Use Cases',
                'features' => [
                    'Valid' => '30 days',
                    'Countries' => 'US, JP, CA, ID, AU, VN, UK, FR, DE',
                    'Protocals' => 'Http(S) / Socks5',
                    'City Level' => 'Yes',
                ],
                'price' => 440,
                'days' => 30,
                'is_active' => true,
            ],
            [
                'name' => '200G',
                'traffic' => 200,
                'description' => '200GB Plan, Suits All Kinds of Use Cases',
                'features' => [
                    'Valid' => '30 days',
                    'Countries' => 'US, JP, CA, ID, AU, VN, UK, FR, DE',
                    'Protocals' => 'Http(S) / Socks5',
                    'City Level' => 'Yes',
                ],
                'price' => 300,
                'days' => 30,
                'is_active' => true,
            ],
            [
                'name' => '80G',
                'traffic' => 80,
                'description' => '80GB Plan, Suits All Kinds of Use Cases',
                'features' => [
                    'Valid' => '30 days',
                    'Countries' => 'US, JP, CA, ID, AU, VN, UK, FR, DE',
                    'Protocals' => 'Http(S) / Socks5',
                    'City Level' => 'Yes',
                ],
                'price' => 160,
                'days' => 30,
                'is_active' => true,
            ],
            [
                'name' => '30G',
                'traffic' => 30,
                'description' => '30GB Plan, Suits All Kinds of Use Cases',
                'features' => [
                    'Valid' => '30 days',
                    'Countries' => 'US, JP, CA, ID, AU, VN, UK, FR, DE',
                    'Protocals' => 'Http(S) / Socks5',
                    'City Level' => 'Yes',
                ],
                'price' => 72,
                'days' => 30,
                'is_active' => true,
            ],
            [
                'name' => '10G',
                'traffic' => 10,
                'description' => '10GB Plan, Suits All Kinds of Use Cases',
                'features' => [
                    'Valid' => '30 days',
                    'Countries' => 'US, JP, CA, ID, AU, VN, UK, FR, DE',
                    'Protocals' => 'Http(S) / Socks5',
                    'City Level' => 'Yes',
                ],
                'price' => 27,
                'days' => 30,
                'is_active' => true,
            ],
            [
                'name' => '5G',
                'traffic' => 5,
                'description' => '5GB Plan, Suits All Kinds of Use Cases',
                'features' => [
                    'Valid' => '30 days',
                    'Countries' => 'US, JP, CA, ID, AU, VN, UK, FR, DE',
                    'Protocals' => 'Http(S) / Socks5',
                    'City Level' => 'Yes',
                ],
                'price' => 15,
                'days' => 30,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(
                ['name' => $plan['name']],
                $plan,
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Plans;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // artist
        Plans::create([
            'name' => 'Free Trial',
            'price' => 0.00,
            'duration' => 3,
            'role' => 'artist',
            'max_users' => 1,
        ]);

        Plans::create([
            'name' => 'Free Trial',
            'price' => 0.00,
            'duration' => 3,
            'role' => 'manager',
            'max_users' => 3,
        ]);

        Plans::create([
            'name' => '1 Month Subscription',
            'price' => 300.00,
            'duration' => 1,
            'type' => 'basic',
            'role' => 'artist',
            'max_users' => 1,
        ]);

        Plans::create([
            'name' => '3 Months Subscription',
            'price' => 500.00,
            'duration' => 3,
            'type' => 'basic',
            'role' => 'artist',
            'max_users' => 1,
        ]);

        Plans::create([
            'name' => '6 Months Subscription',
            'price' => 900.00,
            'duration' => 6,
            'type' => 'basic',
            'role' => 'artist',
            'max_users' => 1,
        ]);

        Plans::create([
            'name' => '12 Months Subscription',
            'price' => 1600.00,
            'duration' => 12,
            'type' => 'basic',
            'role' => 'artist',
            'max_users' => 1,
        ]);
    }
}

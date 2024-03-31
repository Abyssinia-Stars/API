<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\ArtistProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (!User::where("email", "admin@example.com")->exists()) {
            User::factory()->create([
                'name' => 'admin',
                'role' => 'admin',
                'user_name' => 'admin',
                'email' => 'admin@example.com',
            ]);
        }
        User::factory(10)->create();
        ArtistProfile::factory(15)->create();
    }
}

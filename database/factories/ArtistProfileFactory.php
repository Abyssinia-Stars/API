<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ArtistProfile>
 */
class ArtistProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bio' => fake()->realText(),
            'category' => fake()->randomElements(['Muscian', 'Painter', 'Dancer', 'Guitarist']),
            'user_id' => User::factory()->create(['role' => 'artist', 'is_active' => true, 'is_verified' => 'verified'])->id,
            'youtube_links' => fake()->url(),
            'attachments' => fake()->url(),
        ];
    }
}

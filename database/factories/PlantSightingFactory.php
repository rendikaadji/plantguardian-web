<?php

namespace Database\Factories;

use App\Models\PlantSighting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlantSighting>
 */
class PlantSightingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ranger_id' => User::factory(),
            'photo_path' => 'sightings/sample.jpg',
            'verification_status' => 'pending',
            'saved_to_gallery' => true,
        ];
    }
}

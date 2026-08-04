<?php

namespace Database\Factories;

use App\Models\PlantSpecies;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlantSpecies>
 */
class PlantSpeciesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'species_code' => strtoupper($this->faker->unique()->lexify('SP-????')),
            'common_name' => $this->faker->words(2, true),
            'scientific_name' => $this->faker->optional()->words(2, true),
            'description' => $this->faker->sentence(),
            'category' => $this->faker->randomElement(['tree', 'shrub', 'herb', 'grass']),
            'conservation_status' => $this->faker->randomElement(['Common', 'Vulnerable', 'Endangered', 'Protected']),
            'created_by' => User::factory()->create(['role' => 'ranger'])->id,
        ];
    }
}

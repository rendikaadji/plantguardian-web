<?php

namespace Database\Seeders;

use App\Models\PlantSighting;
use App\Models\PlantSpecies;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with initial demo data.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            ['name' => 'Admin Controller', 'password' => Hash::make('password'), 'role' => 'admin']
        );

        $ranger = User::firstOrCreate(
            ['email' => 'ranger@example.com'],
            ['name' => 'Ranger User', 'password' => Hash::make('password'), 'role' => 'ranger']
        );

        $viewer = User::firstOrCreate(
            ['email' => 'viewer@example.com'],
            ['name' => 'Viewer User', 'password' => Hash::make('password'), 'role' => 'viewer']
        );

        $species = PlantSpecies::firstOrCreate(
            ['species_code' => 'MANGIFERA_INDICA'],
            [
                'common_name' => 'Pohon Mangga',
                'scientific_name' => 'Mangifera indica',
                'description' => 'Pohon buah mangga manis lokal.',
                'care_instructions' => 'Siram 2x sehari pada pagi dan sore hari (minimal 2-3 liter air). Beri pupuk organik kompos setiap 2 minggu sekali dan pastikan mendapat paparan sinar matahari penuh (6-8 jam/hari).',
                'conservation_status' => 'Common',
                'created_by' => $ranger->id,
            ]
        );

        PlantSighting::firstOrCreate(
            ['ranger_id' => $ranger->id, 'latitude' => -6.2088, 'longitude' => 106.8456],
            [
                'plant_species_id' => $species->id,
                'photo_path' => 'sightings/mangga.jpg',
                'verification_status' => 'verified',
            ]
        );
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Planting;
use App\Models\PlantSighting;
use App\Models\PlantSpecies;
use Illuminate\Console\Command;

class CleanOrphanSpecies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'species:clean-orphans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned plant species entries that have no associated sightings or plantings.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $allSpecies = PlantSpecies::all();
        $deletedCount = 0;

        foreach ($allSpecies as $species) {
            $hasSightings = PlantSighting::where('plant_species_id', $species->id)->exists();
            $hasPlantings = Planting::where('plant_species_id', $species->id)->exists();

            if (! $hasSightings && ! $hasPlantings) {
                $this->info("Deleting orphaned species ID {$species->id}: {$species->common_name} ({$species->species_code})");
                $species->delete();
                $deletedCount++;
            }
        }

        $this->info("Completed. Deleted {$deletedCount} orphaned plant species entries.");
        return 0;
    }
}

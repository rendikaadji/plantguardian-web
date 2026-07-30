<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plantings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garden_plot_id')->constrained('garden_plots')->onDelete('cascade');
            $table->foreignId('plant_species_id')->nullable()->constrained('plant_species')->onDelete('cascade');
            $table->timestamp('planted_at');
            $table->timestamp('ready_at');
            $table->timestamp('last_watered_at')->nullable();
            $table->enum('status', ['growing', 'ready', 'harvested'])->default('growing');
            $table->timestamp('harvested_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantings');
    }
};

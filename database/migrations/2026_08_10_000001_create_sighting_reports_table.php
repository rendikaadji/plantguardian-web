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
        Schema::create('sighting_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('plant_sighting_id')->nullable()->constrained('plant_sightings')->onDelete('set null');
            $table->string('reason'); // fake_specimen, plant_missing_or_dead, species_mismatch_or_replaced, other
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, resolved_deleted, dismissed
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            // Prevent duplicate pending reports from the same user on the same sighting
            $table->index(['plant_sighting_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sighting_reports');
    }
};

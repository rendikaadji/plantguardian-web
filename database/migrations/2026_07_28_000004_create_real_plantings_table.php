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
        Schema::create('real_plantings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('compost_process_id')->nullable()->constrained('compost_processes')->onDelete('cascade');
            $table->foreignId('plant_species_id')->nullable()->constrained('plant_species')->onDelete('cascade');
            $table->string('photo_path');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('planted_at');
            $table->enum('verification_status', ['self_reported', 'verified', 'rejected'])->default('self_reported');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('real_plantings');
    }
};

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
        Schema::create('compost_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('compost_material_id')->nullable()->constrained('compost_materials')->onDelete('cascade');
            $table->enum('status', ['started', 'in_progress', 'matured', 'used_for_planting', 'abandoned'])->default('started');
            $table->timestamp('started_at');
            $table->timestamp('matured_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compost_processes');
    }
};

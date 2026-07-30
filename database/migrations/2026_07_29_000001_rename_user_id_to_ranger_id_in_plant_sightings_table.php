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
        Schema::table('plant_sightings', function (Blueprint $table) {
            $table->renameColumn('user_id', 'ranger_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plant_sightings', function (Blueprint $table) {
            $table->renameColumn('ranger_id', 'user_id');
        });
    }
};

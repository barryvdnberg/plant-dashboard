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
        Schema::create('plant_environment_soil_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_environment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('soil_type_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plant_environment_soil_type');
    }
};

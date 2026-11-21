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
        Schema::create('plant_properties_pruning_style', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_properties_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pruning_style_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plant_properties_pruning_style');
    }
};

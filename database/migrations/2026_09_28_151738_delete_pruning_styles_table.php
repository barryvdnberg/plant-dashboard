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
        Schema::dropIfExists('plant_properties_pruning_style');
        Schema::dropIfExists('pruning_styles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('pruning_styles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('image_paths')->nullable();
            $table->timestamps();
        });

        Schema::create('plant_properties_pruning_style', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_properties_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pruning_style_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }
};

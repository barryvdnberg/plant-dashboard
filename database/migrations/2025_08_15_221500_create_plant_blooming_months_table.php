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
        Schema::create('plant_blooming_months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_blooming_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->string('value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plant_blooming_months');
    }
};

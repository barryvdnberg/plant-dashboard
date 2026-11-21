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
            $table->foreignId('plant_blooming_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('index'); // 1-12 for Jan-Dec
            // the maximum of unisgned Tiny Integer is 255, so we can store all months in a single byte
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

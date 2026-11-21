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
        Schema::create('foliages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cultivar_id')->constrained()->cascadeOnDelete();
            $table->string('retention')->nullable();
            $table->boolean('seasonal_color_change')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foliages');
    }
};

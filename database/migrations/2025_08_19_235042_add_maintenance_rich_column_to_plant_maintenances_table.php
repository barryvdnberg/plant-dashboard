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
        Schema::table('plant_maintenances', function (Blueprint $table) {
            $table->boolean('maintenance_rich')->default(false)->after('watering_needs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plant_maintenances', function (Blueprint $table) {
            $table->dropColumn(['maintenance_rich']);
        });
    }
};

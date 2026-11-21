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
        Schema::table('plant_environments', function (Blueprint $table) {
            $table->dropColumn(['sun_exposure']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plant_environments', function (Blueprint $table) {
            $table->json('sun_exposure')->nullable()->after('winter_hardiness');
        });
    }
};

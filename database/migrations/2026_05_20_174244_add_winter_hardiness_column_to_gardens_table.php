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
        Schema::table('gardens', function (Blueprint $table) {
            $table->unsignedTinyInteger('winter_hardiness')->default(1)->after('name');
        });

        // remove the default value (it was needed for creation of the column to set values, but after it's not needed)
        Schema::table('gardens', function (Blueprint $table) {
            $table->unsignedTinyInteger('winter_hardiness')->default(null)->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gardens', function (Blueprint $table) {
            $table->dropColumn('winter_hardiness');
        });
    }
};

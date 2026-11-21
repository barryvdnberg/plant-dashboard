<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cultivars', function (Blueprint $table) {
            $table->boolean('is_default')
                ->default(false)
                ->after('plant_id');
        });

        // all cultivars where name is NULL should become the default
        // if more than one cultivar has a NULL name for a single plant,
        // only the first one should be marked as default and we should
        // give a name for the remaining cultivars with NULL names
        $cultivars = DB::table('cultivars')
            ->whereNull('name')
            ->orderBy('plant_id')
            ->get();

        $processedPlants = [];
        foreach ($cultivars as $cultivar) {
            if (!in_array($cultivar->plant_id, $processedPlants)) {
                DB::table('cultivars')
                    ->where('id', $cultivar->id)
                    ->update(['is_default' => true]);
                $processedPlants[] = $cultivar->plant_id;
            } else {
                DB::table('cultivars')
                    ->where('id', $cultivar->id)
                    ->update(['name' => 'Unnamed']);
            }
        }
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cultivars', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });

        // cultivars with Unnamed names should become null again
        DB::table('cultivars')
            ->where('name', 'Unnamed')
            ->update(['name' => null]);
    }
};

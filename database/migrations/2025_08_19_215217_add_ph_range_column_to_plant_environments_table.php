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
        Schema::table('plant_environments', function (Blueprint $table) {
            $table->json('ph_range')->after('winter_hardiness');
        });


        DB::table('plant_environments')->select('id', 'ph_minimum', 'ph_maximum')->orderBy('id')->chunk(100, function($rows) {
            foreach ($rows as $row) {
                $min = $row->ph_minimum ?? 6;
                $max = $row->ph_maximum ?? 8;

                DB::table('plant_environments')
                    ->where('id', $row->id)
                    ->update([
                        'ph_range' => json_encode([$min, $max])
                    ]);
            }
        });

        Schema::table('plant_environments', function (Blueprint $table) {
            $table->dropColumn(['ph_minimum', 'ph_maximum']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plant_environments', function (Blueprint $table) {
            $table->float('ph_maximum')->nullable()->after('cultivar_id');
            $table->float('ph_minimum')->nullable()->after('cultivar_id');
        });
        
        DB::table('plant_environments')->select('id', 'ph_range')->orderBy('id')->chunk(100, function($rows) {
            foreach ($rows as $row) {
                $range = json_decode($row->ph_range, true) ?? [6, 8];
                $min = $range[0] ?? 6;
                $max = $range[1] ?? 8;

                if ($min == 6 && $max == 8) continue;

                DB::table('plant_environments')
                    ->where('id', $row->id)
                    ->update([
                        'ph_minimum' => $min,
                        'ph_maximum' => $max
                    ]);
            }
        });

        Schema::table('plant_environments', function (Blueprint $table) {
            $table->dropColumn(['ph_range']);
        });
    }
};

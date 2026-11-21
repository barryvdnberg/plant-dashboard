<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $map = [
            1 => 'januari',
            2 => 'februari',
            3 =>'march',
            4 => 'april',
            5 => 'may',
            6 => 'june',
            7 => 'july',
            8 => 'august',
            9 => 'september',
            10 => 'october',
            11 => 'november',
            12 => 'december',
        ];

        DB::table('plant_bloomings')->select('id', 'months')->orderBy('id')->chunk(100, function ($rows) use ($map) {
            foreach ($rows as $row) {
                $months = json_decode($row->months, true) ?? [];

                $converted = array_map(fn($m) => $map[$m] ?? $m, $months);

                DB::table('plant_bloomings')
                    ->where('id', $row->id)
                    ->update([
                        'months' => json_encode($converted)
                    ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $map = [
            'januari'   => 1,
            'februari'  => 2,
            'march'     => 3,
            'april'     => 4,
            'may'       => 5,
            'juni'      => 6,
            'july'      => 7,
            'augustus'  => 8,
            'september' => 9,
            'october'   => 10,
            'november'  => 11,
            'december'  => 12,
        ];

        DB::table('plant_bloomings')->select('id', 'months')->orderBy('id')->chunk(100, function ($rows) use ($map) {
            foreach ($rows as $row) {
                $months = json_decode($row->months, true) ?? [];

                $converted = array_map(fn($m) => $map[$m] ?? $m, $months);

                DB::table('plant_bloomings')
                    ->where('id', $row->id)
                    ->update([
                        'months' => json_encode($converted),
                    ]);
            }
        });
    }
};

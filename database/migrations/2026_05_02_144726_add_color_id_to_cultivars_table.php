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
            $table->foreignId('color_id')
                ->nullable()
                ->after('name')
                ->constrained('colors')
                ->nullOnDelete();
        });

        DB::table('cultivars')
            ->select(['id', 'name'])
            ->lazyById()
            ->filter(fn ($cultivar) => ctype_digit((string) $cultivar->name))
            ->each(function ($cultivar) {
                DB::table('cultivars')
                    ->where('id', $cultivar->id)
                    ->update([
                        'color_id' => (int) $cultivar->name,
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('cultivars')
            ->whereNotNull('color_id')
            ->where('name', 'Unnamed Cultivar')
            ->update([
                'name' => DB::raw('color_id')
            ]);
            
        Schema::table('cultivars', function (Blueprint $table) {
            $table->dropForeign(['color_id']);
            $table->dropColumn('color_id');
        });
    }
};

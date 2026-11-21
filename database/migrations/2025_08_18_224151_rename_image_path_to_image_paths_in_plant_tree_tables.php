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
        foreach (['families', 'genera', 'species', 'plants', 'cultivars'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->json('image_paths')->nullable()->after('image_path');
            });

            DB::table($tableName)->select('id', 'image_path')->orderBy('id')->chunk(100, function ($rows) use ($tableName) {
                foreach ($rows as $row) {
                    if (!empty($row->image_path)) {
                        DB::table($tableName)
                            ->where('id', $row->id)
                            ->update([
                                'image_paths' => json_encode([$row->image_path])
                            ]);
                    }
                }
            });

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('image_path');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['families', 'genera', 'species', 'plants', 'cultivars'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('image_path')->after('image_paths');
            });

            DB::table($tableName)->select('id', 'image_paths')->orderBy('id')->chunk(100, function($rows) use ($tableName) {
                foreach ($rows as $row) {
                    if (!empty($row->image_paths)) {
                        $paths = json_decode($row->image_paths, true);
                        $first = $paths[0] ?? null;

                        if ($first) {
                            DB::table($tableName)
                                ->where('id', $row->id)
                                ->update([
                                    'image_path' => $first
                                ]);
                        }
                    }
                }
            });

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('image_paths');
            });
        }
    }
};

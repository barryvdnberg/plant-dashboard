<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('locale', 35)->nullable();
            $table->string('name_display_preference', 32)->default('scientific');
            $table->json('name_display_overrides')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['locale', 'name_display_preference', 'name_display_overrides']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_edition_stats', function (Blueprint $table) {
            $table->integer('mvp_count')->default(0)->after('total_stumpings');
        });
    }

    public function down(): void
    {
        Schema::table('player_edition_stats', function (Blueprint $table) {
            $table->dropColumn('mvp_count');
        });
    }
};

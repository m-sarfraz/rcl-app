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
        Schema::table('fines', function (Blueprint $table) {
            // Drop the old NOT NULL FK constraint on player_id
            $table->dropForeign(['player_id']);
            $table->foreignId('team_id')->nullable()->after('edition_id')->constrained('teams')->nullOnDelete();
        });

        // Make player_id nullable
        \Illuminate\Support\Facades\DB::statement(
            'ALTER TABLE fines MODIFY player_id BIGINT UNSIGNED NULL'
        );

        Schema::table('fines', function (Blueprint $table) {
            $table->foreign('player_id')->references('id')->on('players')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fines', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
            $table->dropForeign(['player_id']);
        });

        \Illuminate\Support\Facades\DB::statement(
            'ALTER TABLE fines MODIFY player_id BIGINT UNSIGNED NOT NULL'
        );

        Schema::table('fines', function (Blueprint $table) {
            $table->foreign('player_id')->references('id')->on('players')->cascadeOnDelete();
        });
    }
};

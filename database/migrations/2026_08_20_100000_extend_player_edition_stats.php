<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The aggregate table could report averages but not the raw counters they were
 * derived from, which made every recomputation lossy and left MVP ranking with
 * nowhere to live. These columns close that gap.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_edition_stats', function (Blueprint $table) {
            $table->integer('balls_faced')->default(0)->after('highest_score');
            $table->integer('not_outs')->default(0)->after('balls_faced');
            $table->integer('runs_conceded')->default(0)->after('total_maidens');
            $table->integer('balls_bowled')->default(0)->after('runs_conceded');
            $table->integer('best_bowling_wickets')->default(0)->after('balls_bowled');
            $table->integer('best_bowling_runs')->default(0)->after('best_bowling_wickets');
            $table->decimal('mvp_points', 10, 2)->default(0)->after('mvp_count');

            $table->index(['edition_id', 'mvp_points']);
        });
    }

    public function down(): void
    {
        Schema::table('player_edition_stats', function (Blueprint $table) {
            $table->dropIndex(['edition_id', 'mvp_points']);
            $table->dropColumn([
                'balls_faced', 'not_outs', 'runs_conceded', 'balls_bowled',
                'best_bowling_wickets', 'best_bowling_runs', 'mvp_points',
            ]);
        });
    }
};

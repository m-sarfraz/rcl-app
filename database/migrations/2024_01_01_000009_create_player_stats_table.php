<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Per-innings batting scorecard
        Schema::create('batting_scorecards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('innings_id')->constrained()->cascadeOnDelete();
            $table->foreignId('match_id')->constrained('cricket_matches')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->integer('batting_position')->nullable();
            $table->integer('runs_scored')->default(0);
            $table->integer('balls_faced')->default(0);
            $table->integer('fours')->default(0);
            $table->integer('sixes')->default(0);
            $table->decimal('strike_rate', 8, 2)->default(0);
            $table->enum('dismissal_type', ['bowled', 'caught', 'run_out', 'lbw', 'stumped', 'hit_wicket', 'not_out', 'retired_hurt', 'did_not_bat'])->default('not_out');
            $table->foreignId('bowled_by_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('caught_by_id')->nullable()->constrained('players')->nullOnDelete();
            $table->boolean('is_fifty')->default(false);
            $table->boolean('is_century')->default(false);
            $table->boolean('hat_trick_sixes')->default(false);
            $table->boolean('five_sixes_in_over')->default(false);
            $table->timestamps();

            $table->unique(['innings_id', 'player_id']);
            $table->index(['match_id', 'player_id']);
        });

        // Per-innings bowling scorecard
        Schema::create('bowling_scorecards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('innings_id')->constrained()->cascadeOnDelete();
            $table->foreignId('match_id')->constrained('cricket_matches')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->integer('overs_bowled_balls')->default(0);
            $table->decimal('overs_bowled', 5, 2)->default(0);
            $table->integer('maidens')->default(0);
            $table->integer('runs_conceded')->default(0);
            $table->integer('wickets')->default(0);
            $table->integer('wides')->default(0);
            $table->integer('no_balls')->default(0);
            $table->decimal('economy', 8, 2)->default(0);
            $table->boolean('hat_trick_wickets')->default(false);
            $table->boolean('five_wicket_haul')->default(false);
            $table->timestamps();

            $table->unique(['innings_id', 'player_id']);
            $table->index(['match_id', 'player_id']);
        });

        // Fielding stats per match
        Schema::create('fielding_scorecards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('cricket_matches')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->integer('catches')->default(0);
            $table->integer('run_outs')->default(0);
            $table->integer('stumpings')->default(0);
            $table->timestamps();

            $table->unique(['match_id', 'player_id']);
        });

        // Aggregated player stats per edition
        Schema::create('player_edition_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            // Batting
            $table->integer('matches_played')->default(0);
            $table->integer('innings_batted')->default(0);
            $table->integer('total_runs')->default(0);
            $table->integer('highest_score')->default(0);
            $table->decimal('batting_average', 8, 2)->default(0);
            $table->decimal('batting_strike_rate', 8, 2)->default(0);
            $table->integer('fifties')->default(0);
            $table->integer('centuries')->default(0);
            $table->integer('total_fours')->default(0);
            $table->integer('total_sixes')->default(0);
            $table->integer('hat_trick_sixes_count')->default(0);
            $table->integer('five_sixes_in_over_count')->default(0);
            // Bowling
            $table->integer('innings_bowled')->default(0);
            $table->decimal('overs_bowled', 8, 2)->default(0);
            $table->integer('total_wickets')->default(0);
            $table->integer('total_maidens')->default(0);
            $table->decimal('bowling_average', 8, 2)->default(0);
            $table->decimal('bowling_economy', 8, 2)->default(0);
            $table->integer('hat_trick_wickets_count')->default(0);
            $table->integer('five_wicket_hauls')->default(0);
            // Fielding
            $table->integer('total_catches')->default(0);
            $table->integer('total_run_outs')->default(0);
            $table->integer('total_stumpings')->default(0);
            $table->timestamps();

            $table->unique(['player_id', 'edition_id']);
            $table->index(['edition_id', 'total_runs']);
            $table->index(['edition_id', 'total_wickets']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_edition_stats');
        Schema::dropIfExists('fielding_scorecards');
        Schema::dropIfExists('bowling_scorecards');
        Schema::dropIfExists('batting_scorecards');
    }
};

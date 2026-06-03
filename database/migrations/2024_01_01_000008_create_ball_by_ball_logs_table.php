<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ball_by_ball_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('innings_id')->constrained()->cascadeOnDelete();
            $table->foreignId('match_id')->constrained('cricket_matches')->cascadeOnDelete();
            $table->foreignId('bowler_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('batsman_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('non_striker_id')->nullable()->constrained('players')->nullOnDelete();
            $table->integer('over_number');
            $table->integer('ball_number');
            $table->integer('runs_scored')->default(0);
            $table->boolean('is_wicket')->default(false);
            $table->enum('wicket_type', ['bowled', 'caught', 'run_out', 'lbw', 'stumped', 'hit_wicket', 'obstructing_field', 'handled_ball', 'timed_out'])->nullable();
            $table->foreignId('fielder_id')->nullable()->constrained('players')->nullOnDelete();
            $table->boolean('is_wide')->default(false);
            $table->boolean('is_no_ball')->default(false);
            $table->boolean('is_bye')->default(false);
            $table->boolean('is_leg_bye')->default(false);
            $table->boolean('is_penalty')->default(false);
            $table->integer('extra_runs')->default(0);
            $table->boolean('is_four')->default(false);
            $table->boolean('is_six')->default(false);
            $table->integer('batting_team_score_after')->default(0);
            $table->integer('batting_team_wickets_after')->default(0);
            $table->text('commentary')->nullable();
            $table->timestamps();

            $table->index(['innings_id', 'over_number', 'ball_number']);
            $table->index(['match_id', 'bowler_id']);
            $table->index(['match_id', 'batsman_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ball_by_ball_logs');
    }
};

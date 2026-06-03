<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cricket_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('home_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('match_number');
            $table->enum('match_type', ['group', 'quarter_final', 'semi_final', 'final'])->default('group');
            $table->string('venue');
            $table->dateTime('scheduled_at');
            $table->enum('status', ['upcoming', 'live', 'completed', 'abandoned', 'postponed'])->default('upcoming');
            $table->integer('overs_per_side')->default(10);
            $table->foreignId('toss_winner_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->enum('toss_decision', ['bat', 'field'])->nullable();
            $table->foreignId('winner_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->enum('result_type', ['runs', 'wickets', 'tie', 'no_result', 'super_over'])->nullable();
            $table->integer('result_margin')->nullable();
            $table->text('result_description')->nullable();
            $table->foreignId('umpire1_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('umpire2_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('scorer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('man_of_match_player_id')->nullable();
            $table->boolean('is_super_over')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['edition_id', 'status']);
            $table->index(['home_team_id', 'away_team_id']);
            $table->index('scheduled_at');
        });

        // Innings table
        Schema::create('innings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('cricket_matches')->cascadeOnDelete();
            $table->foreignId('batting_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('bowling_team_id')->constrained('teams')->cascadeOnDelete();
            $table->integer('innings_number');
            $table->integer('total_runs')->default(0);
            $table->integer('total_wickets')->default(0);
            $table->integer('total_balls')->default(0);
            $table->decimal('overs_faced', 5, 2)->default(0);
            $table->decimal('run_rate', 8, 2)->default(0);
            $table->integer('extras_wides')->default(0);
            $table->integer('extras_no_balls')->default(0);
            $table->integer('extras_byes')->default(0);
            $table->integer('extras_leg_byes')->default(0);
            $table->integer('extras_penalty')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->integer('target')->nullable();
            $table->timestamps();

            $table->unique(['match_id', 'innings_number']);
            $table->index('match_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('innings');
        Schema::dropIfExists('cricket_matches');
    }
};

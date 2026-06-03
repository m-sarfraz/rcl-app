<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('jersey_number')->nullable();
            $table->string('photo')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('role', ['batsman', 'bowler', 'all_rounder', 'wicket_keeper'])->default('batsman');
            $table->enum('batting_style', ['right_hand', 'left_hand'])->default('right_hand');
            $table->enum('bowling_style', ['right_arm_fast', 'right_arm_medium', 'right_arm_spin', 'left_arm_fast', 'left_arm_medium', 'left_arm_spin', 'none'])->default('none');
            $table->enum('bowling_action_status', ['legal', 'flagged', 'banned'])->default('legal');
            $table->string('phone')->nullable();
            $table->text('bio')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'is_active']);
            $table->index('bowling_action_status');
        });

        // Player-Team assignment per edition with transfer log
        Schema::create('player_team_editions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_captain')->default(false);
            $table->boolean('is_vice_captain')->default(false);
            $table->string('transfer_from_team')->nullable();
            $table->text('transfer_reason')->nullable();
            $table->timestamps();

            $table->unique(['player_id', 'edition_id']);
            $table->index(['team_id', 'edition_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_team_editions');
        Schema::dropIfExists('players');
    }
};

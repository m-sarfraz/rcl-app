<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The client-side scoring engine needs a couple of match-level facts the schema
 * never carried: who is actually in the playing XI, and when a match was sealed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cricket_matches', function (Blueprint $table) {
            $table->timestamp('finalized_at')->nullable()->after('notes');
            $table->string('finalized_by')->nullable()->after('finalized_at');
        });

        Schema::create('match_squads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('cricket_matches')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->integer('batting_order')->nullable();
            $table->boolean('is_captain')->default(false);
            $table->boolean('is_wicket_keeper')->default(false);
            $table->timestamps();

            $table->unique(['match_id', 'player_id']);
            $table->index(['match_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_squads');
        Schema::table('cricket_matches', function (Blueprint $table) {
            $table->dropColumn(['finalized_at', 'finalized_by']);
        });
    }
};

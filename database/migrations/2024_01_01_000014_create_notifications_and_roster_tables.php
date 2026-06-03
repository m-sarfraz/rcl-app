<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->enum('type', ['info','live_update','suspension','fine','announcement'])->default('info');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_ticker')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['is_active','is_ticker']);
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('player_edition_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('jersey_number', 10)->nullable();
            $table->unsignedBigInteger('transfer_from_team_id')->nullable();
            $table->timestamps();

            $table->unique(['player_id','edition_id']);
            $table->index(['team_id','edition_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_edition_teams');
        Schema::dropIfExists('notifications');
    }
};

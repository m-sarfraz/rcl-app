<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demerit_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->nullable()->constrained()->nullOnDelete();
            $table->string('target_type')->default('player'); // 'player', 'team', 'umpire', or custom category name
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('player_id')->nullable()->constrained()->nullOnDelete();
            $table->string('target_name')->nullable(); // For umpire or custom tab entities
            $table->foreignId('match_id')->nullable()->constrained('cricket_matches')->nullOnDelete();
            $table->unsignedInteger('points')->default(1);
            $table->text('reason');
            $table->date('incident_date');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['target_type', 'is_active']);
            $table->index(['team_id', 'is_active']);
            $table->index(['player_id', 'is_active']);
            $table->index(['edition_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demerit_points');
    }
};

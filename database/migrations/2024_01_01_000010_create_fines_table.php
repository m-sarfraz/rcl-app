<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('match_id')->nullable()->constrained('cricket_matches')->nullOnDelete();
            $table->enum('violation_type', ['chucking', 'code_of_conduct', 'disciplinary_card', 'misconduct', 'other']);
            $table->enum('card_type', ['yellow', 'red', 'none'])->default('none');
            $table->text('description');
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('status', ['unpaid', 'paid', 'waived'])->default('unpaid');
            $table->date('due_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['player_id', 'status']);
            $table->index(['edition_id', 'status']);
        });

        // Player suspension/ban tracker
        Schema::create('player_suspensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fine_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['suspension', 'ban']);
            $table->string('reason');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('matches_suspended')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['player_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_suspensions');
        Schema::dropIfExists('fines');
    }
};

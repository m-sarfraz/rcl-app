<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banned_bowlers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->text('reason');
            $table->date('banned_from');
            $table->date('banned_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['player_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banned_bowlers');
    }
};

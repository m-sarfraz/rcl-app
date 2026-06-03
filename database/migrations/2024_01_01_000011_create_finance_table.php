<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['income', 'expense']);
            $table->enum('category', ['entry_fee', 'sponsorship', 'umpire_fee', 'scorer_fee', 'groundsman_fee', 'equipment', 'prize_money', 'fine_collection', 'other']);
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('match_id')->nullable()->constrained('cricket_matches')->nullOnDelete();
            $table->string('reference_number')->nullable();
            $table->date('transaction_date');
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['edition_id', 'type', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
    }
};

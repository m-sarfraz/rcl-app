<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('match_id')->nullable()->constrained('cricket_matches')->nullOnDelete();
            $table->string('question');
            $table->text('description')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_anonymous')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['edition_id', 'is_active']);
        });

        Schema::create('poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->string('option_text');
            $table->string('option_image')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('poll_option_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('session_token')->nullable();
            $table->timestamps();

            $table->index(['poll_id', 'user_id']);
            $table->index(['poll_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_votes');
        Schema::dropIfExists('poll_options');
        Schema::dropIfExists('polls');
    }
};

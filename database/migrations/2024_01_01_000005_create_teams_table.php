<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('village_name');
            $table->string('short_code', 5);
            $table->string('logo')->nullable();
            $table->string('primary_color', 7)->default('#1a1a2e');
            $table->string('secondary_color', 7)->default('#16213e');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'village_name']);
        });

        // Edition-Team pivot (which teams participate in which edition)
        Schema::create('edition_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->integer('group_number')->nullable();
            $table->timestamps();

            $table->unique(['edition_id', 'team_id']);
            $table->index('edition_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edition_teams');
        Schema::dropIfExists('teams');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('edition_number');
            $table->string('host_village');
            $table->string('thumbnail')->nullable();
            $table->string('banner')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['upcoming', 'active', 'completed', 'archived'])->default('upcoming');
            $table->text('description')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_current']);
            $table->index('edition_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('editions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vcc_cabinets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role_title');
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->string('village')->nullable();
            $table->string('phone')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vcc_cabinets');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->string('label')->nullable()->after('module');
        });
    }

    public function down(): void
    {
        Schema::table('permissions', fn(Blueprint $t) => $t->dropColumn('label'));
        Schema::table('roles', fn(Blueprint $t) => $t->dropColumn('slug'));
    }
};

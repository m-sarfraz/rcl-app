<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Two gaps that made the ball log unusable as a source of truth:
 *
 *  1. No way to say *who* got out — on a run-out it is often the non-striker,
 *     so assuming `batsman_id` silently credited the wrong dismissal.
 *  2. No client identifier, so a phone retrying a delivery after a dropped
 *     connection would post the same ball twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ball_by_ball_logs', function (Blueprint $table) {
            $table->foreignId('out_player_id')->nullable()->after('wicket_type')
                ->constrained('players')->nullOnDelete();
            $table->uuid('client_uuid')->nullable()->unique()->after('id');
        });

        // Widening a MySQL ENUM needs raw DDL. SQLite (used by the test suite)
        // stores enums as text, so there is nothing to widen there.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE ball_by_ball_logs MODIFY wicket_type ENUM(
                    'bowled','caught','run_out','lbw','stumped','hit_wicket',
                    'obstructing_field','handled_ball','timed_out','retired_hurt'
                ) NULL"
            );
        }
    }

    public function down(): void
    {
        Schema::table('ball_by_ball_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('out_player_id');
            $table->dropUnique(['client_uuid']);
            $table->dropColumn('client_uuid');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE ball_by_ball_logs MODIFY wicket_type ENUM(
                    'bowled','caught','run_out','lbw','stumped','hit_wicket',
                    'obstructing_field','handled_ball','timed_out'
                ) NULL"
            );
        }
    }
};

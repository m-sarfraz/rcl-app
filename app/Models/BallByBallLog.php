<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BallByBallLog extends Model
{
    protected $fillable = [
        'innings_id', 'match_id', 'bowler_id', 'batsman_id', 'non_striker_id',
        'over_number', 'ball_number', 'runs_scored', 'is_wicket', 'wicket_type',
        'fielder_id', 'is_wide', 'is_no_ball', 'is_bye', 'is_leg_bye', 'is_penalty',
        'extra_runs', 'is_four', 'is_six', 'batting_team_score_after',
        'batting_team_wickets_after', 'commentary',
    ];

    protected $casts = [
        'is_wicket' => 'boolean',
        'is_wide' => 'boolean',
        'is_no_ball' => 'boolean',
        'is_bye' => 'boolean',
        'is_leg_bye' => 'boolean',
        'is_penalty' => 'boolean',
        'is_four' => 'boolean',
        'is_six' => 'boolean',
    ];

    public function innings(): BelongsTo
    {
        return $this->belongsTo(Innings::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(CricketMatch::class, 'match_id');
    }

    public function bowler(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'bowler_id');
    }

    public function batsman(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'batsman_id');
    }

    public function nonStriker(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'non_striker_id');
    }

    public function fielder(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'fielder_id');
    }

    public function isLegalDelivery(): bool
    {
        return !$this->is_wide && !$this->is_no_ball;
    }

    public function getTotalRunsAttribute(): int
    {
        return $this->runs_scored + $this->extra_runs;
    }
}

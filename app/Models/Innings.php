<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Innings extends Model
{
    protected $fillable = [
        'match_id', 'batting_team_id', 'bowling_team_id', 'innings_number',
        'total_runs', 'total_wickets', 'total_balls', 'overs_faced', 'run_rate',
        'extras_wides', 'extras_no_balls', 'extras_byes', 'extras_leg_byes',
        'extras_penalty', 'is_completed', 'target',
    ];

    protected $casts = ['is_completed' => 'boolean'];

    public function match(): BelongsTo
    {
        return $this->belongsTo(CricketMatch::class, 'match_id');
    }

    public function battingTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'batting_team_id');
    }

    public function bowlingTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'bowling_team_id');
    }

    public function ballByBallLogs(): HasMany
    {
        return $this->hasMany(BallByBallLog::class);
    }

    public function ballByBall(): HasMany
    {
        return $this->hasMany(BallByBallLog::class);
    }

    public function battingScorecards(): HasMany
    {
        return $this->hasMany(BattingScorecard::class);
    }

    public function bowlingScorecards(): HasMany
    {
        return $this->hasMany(BowlingScorecard::class);
    }

    public function getTotalExtrasAttribute(): int
    {
        return $this->extras_wides + $this->extras_no_balls +
               $this->extras_byes + $this->extras_leg_byes + $this->extras_penalty;
    }
}

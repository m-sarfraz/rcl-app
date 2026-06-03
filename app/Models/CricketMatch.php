<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CricketMatch extends Model
{
    use SoftDeletes;

    protected $table = 'cricket_matches';

    protected $fillable = [
        'edition_id', 'home_team_id', 'away_team_id', 'match_number', 'match_type',
        'venue', 'scheduled_at', 'status', 'overs_per_side', 'toss_winner_id',
        'toss_decision', 'winner_id', 'result_type', 'result_margin',
        'result_description', 'umpire1_id', 'umpire2_id', 'scorer_id',
        'man_of_match_player_id', 'is_super_over', 'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'is_super_over' => 'boolean',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_id');
    }

    public function tossWinner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'toss_winner_id');
    }

    public function umpire1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'umpire1_id');
    }

    public function umpire2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'umpire2_id');
    }

    public function scorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scorer_id');
    }

    public function innings(): HasMany
    {
        return $this->hasMany(Innings::class, 'match_id');
    }

    public function ballByBallLogs(): HasMany
    {
        return $this->hasMany(BallByBallLog::class, 'match_id');
    }

    public function battingScorecards(): HasMany
    {
        return $this->hasMany(BattingScorecard::class, 'match_id');
    }

    public function bowlingScorecards(): HasMany
    {
        return $this->hasMany(BowlingScorecard::class, 'match_id');
    }

    public function scopeLive($query)
    {
        return $query->where('status', 'live');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}

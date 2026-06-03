<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BowlingScorecard extends Model
{
    protected $fillable = [
        'innings_id', 'match_id', 'player_id', 'team_id',
        'overs_bowled_balls', 'overs_bowled', 'maidens', 'runs_conceded',
        'wickets', 'wides', 'no_balls', 'economy',
        'hat_trick_wickets', 'five_wicket_haul',
    ];

    protected $casts = [
        'hat_trick_wickets' => 'boolean',
        'five_wicket_haul' => 'boolean',
    ];

    public function innings(): BelongsTo
    {
        return $this->belongsTo(Innings::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getOversAttribute(): string
    {
        $balls = $this->overs_bowled_balls;
        return floor($balls / 6) . '.' . ($balls % 6);
    }
}

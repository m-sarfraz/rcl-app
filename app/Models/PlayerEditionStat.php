<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerEditionStat extends Model
{
    protected $fillable = [
        'player_id', 'edition_id', 'team_id',
        'matches_played', 'innings_batted', 'total_runs', 'highest_score',
        'batting_average', 'batting_strike_rate', 'fifties', 'centuries',
        'total_fours', 'total_sixes', 'hat_trick_sixes_count', 'five_sixes_in_over_count',
        'innings_bowled', 'overs_bowled', 'total_wickets', 'total_maidens',
        'bowling_average', 'bowling_economy', 'hat_trick_wickets_count', 'five_wicket_hauls',
        'total_catches', 'total_run_outs', 'total_stumpings', 'mvp_count', 'mvp_points',
        'balls_faced', 'not_outs', 'runs_conceded', 'balls_bowled',
        'best_bowling_wickets', 'best_bowling_runs',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getStrikeRateAttribute(): float
    {
        return $this->batting_strike_rate ?? 0;
    }

    public function getEconomyAttribute(): float
    {
        return $this->bowling_economy ?? 0;
    }
}

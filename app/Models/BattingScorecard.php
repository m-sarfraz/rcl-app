<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BattingScorecard extends Model
{
    protected $fillable = [
        'innings_id', 'match_id', 'player_id', 'team_id', 'batting_position',
        'runs_scored', 'balls_faced', 'fours', 'sixes', 'strike_rate',
        'dismissal_type', 'bowled_by_id', 'caught_by_id',
        'is_fifty', 'is_century', 'hat_trick_sixes', 'five_sixes_in_over',
    ];

    protected $casts = [
        'is_fifty' => 'boolean',
        'is_century' => 'boolean',
        'hat_trick_sixes' => 'boolean',
        'five_sixes_in_over' => 'boolean',
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

    public function bowledBy(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'bowled_by_id');
    }

    public function caughtBy(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'caught_by_id');
    }

    public function getIsOutAttribute(): bool
    {
        return !in_array($this->dismissal_type, ['not_out', 'retired_hurt', 'did_not_bat']);
    }

    public function getRunsAttribute(): int
    {
        return $this->runs_scored;
    }
}

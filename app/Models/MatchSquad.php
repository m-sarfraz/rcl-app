<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The XI actually named for a match, captured by the scoring console at setup
 * time. Distinct from the edition roster, which is the whole squad.
 */
class MatchSquad extends Model
{
    protected $table = 'match_squads';

    protected $fillable = [
        'match_id', 'team_id', 'player_id',
        'batting_order', 'is_captain', 'is_wicket_keeper',
    ];

    protected $casts = [
        'is_captain'       => 'boolean',
        'is_wicket_keeper' => 'boolean',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(CricketMatch::class, 'match_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}

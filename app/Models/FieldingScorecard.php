<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldingScorecard extends Model
{
    protected $fillable = [
        'match_id', 'player_id', 'team_id', 'catches', 'run_outs', 'stumpings',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(CricketMatch::class, 'match_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A roster entry: this player, for this club, in this edition.
 *
 * Note the table name — `player_team_editions`. A second, empty table called
 * `player_edition_teams` exists from an old migration and is not used by
 * anything; the fillable list here matches the table that actually holds data.
 */
class PlayerEditionTeam extends Model
{
    protected $table = 'player_team_editions';

    protected $fillable = [
        'player_id', 'edition_id', 'team_id',
        'is_captain', 'is_vice_captain',
        'transfer_from_team', 'transfer_reason',
    ];

    protected $casts = [
        'is_captain'      => 'boolean',
        'is_vice_captain' => 'boolean',
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
}

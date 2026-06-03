<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Player extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'father_name', 'jersey_number', 'photo', 'date_of_birth', 'role',
        'batting_style', 'bowling_style', 'bowling_action_status',
        'phone', 'bio', 'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
    ];

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'player_team_editions')
            ->withPivot('edition_id', 'is_captain', 'is_vice_captain', 'transfer_from_team', 'transfer_reason')
            ->withTimestamps();
    }

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }

    public function suspensions(): HasMany
    {
        return $this->hasMany(PlayerSuspension::class);
    }

    public function battingScorecards(): HasMany
    {
        return $this->hasMany(BattingScorecard::class);
    }

    public function bowlingScorecards(): HasMany
    {
        return $this->hasMany(BowlingScorecard::class);
    }

    public function editionStats(): HasMany
    {
        return $this->hasMany(PlayerEditionStat::class);
    }

    public function isEligible(): bool
    {
        $hasUnpaidFine = $this->fines()->where('status', 'unpaid')->exists();
        $isSuspended = $this->suspensions()->where('is_active', true)->exists();
        $isBanned = $this->bowling_action_status === 'banned';

        return !$hasUnpaidFine && !$isSuspended && !$isBanned;
    }

    public function scopeEligible($query)
    {
        return $query->where('bowling_action_status', '!=', 'banned')
            ->whereDoesntHave('fines', fn($q) => $q->where('status', 'unpaid'))
            ->whereDoesntHave('suspensions', fn($q) => $q->where('is_active', true));
    }
}

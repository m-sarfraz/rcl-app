<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
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

    /** Raw roster rows (player ⇄ team ⇄ edition), newest edition first. */
    public function rosterEntries(): HasMany
    {
        return $this->hasMany(PlayerEditionTeam::class, 'player_id')->orderByDesc('edition_id');
    }

    /**
     * The team this player last turned out for. Eager-loadable, unlike a
     * computed accessor, which matters because the API lists hundreds of players.
     */
    public function currentTeam(): HasOneThrough
    {
        return $this->hasOneThrough(
            Team::class,
            PlayerEditionTeam::class,
            'player_id',   // FK on player_team_editions → players
            'id',          // FK on teams
            'id',          // local key on players
            'team_id'      // local key on player_team_editions
        )->orderByDesc('player_team_editions.edition_id');
    }

    public function bans(): HasMany
    {
        return $this->hasMany(BannedBowler::class);
    }

    public function activeBans(): HasMany
    {
        return $this->bans()->where('is_active', true);
    }

    public function demeritPoints(): HasMany
    {
        return $this->hasMany(DemeritPoint::class);
    }

    public function activeDemeritPoints(): HasMany
    {
        return $this->demeritPoints()->where('is_active', true);
    }

    /**
     * Why this player may not be picked, or null when they are available.
     * Mirrors scopeEligible() so the console and the query agree.
     */
    public function ineligibilityReason(): ?string
    {
        if ($this->bowling_action_status === 'banned') {
            return 'Banned bowling action';
        }
        if ($this->relationLoaded('suspensions')
            ? $this->suspensions->where('is_active', true)->isNotEmpty()
            : $this->suspensions()->where('is_active', true)->exists()) {
            return 'Suspended';
        }
        if ($this->relationLoaded('fines')
            ? $this->fines->where('status', 'unpaid')->isNotEmpty()
            : $this->fines()->where('status', 'unpaid')->exists()) {
            return 'Unpaid fine';
        }
        if ($this->relationLoaded('demeritPoints')
            ? $this->demeritPoints->where('is_active', true)->sum('points') >= DemeritPoint::THRESHOLD_BAN
            : $this->activeDemeritPoints()->sum('points') >= DemeritPoint::THRESHOLD_BAN) {
            return 'Banned (Demerit points threshold reached)';
        }

        return null;
    }

    public function isEligible(): bool
    {
        $hasUnpaidFine = $this->fines()->where('status', 'unpaid')->exists();
        $isSuspended = $this->suspensions()->where('is_active', true)->exists();
        $isBanned = $this->bowling_action_status === 'banned';
        $isDemeritBanned = $this->activeDemeritPoints()->sum('points') >= DemeritPoint::THRESHOLD_BAN;

        return !$hasUnpaidFine && !$isSuspended && !$isBanned && !$isDemeritBanned;
    }

    public function scopeEligible($query)
    {
        return $query->where('bowling_action_status', '!=', 'banned')
            ->whereDoesntHave('fines', fn($q) => $q->where('status', 'unpaid'))
            ->whereDoesntHave('suspensions', fn($q) => $q->where('is_active', true));
    }
}

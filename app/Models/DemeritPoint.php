<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemeritPoint extends Model
{
    use HasFactory;

    public const THRESHOLD_BAN = 3;
    public const RULE_NOTE = 'Once reached 3 demerit points, team/player will be banned to play till further decision.';

    protected $fillable = [
        'edition_id',
        'target_type',
        'team_id',
        'player_id',
        'target_name',
        'match_id',
        'points',
        'reason',
        'incident_date',
        'is_active',
        'notes',
        'issued_by',
    ];

    protected $casts = [
        'points'        => 'integer',
        'incident_date' => 'date',
        'is_active'     => 'boolean',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(CricketMatch::class, 'match_id');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getEntityNameAttribute(): string
    {
        if ($this->target_type === 'player') {
            return $this->player?->name ?? 'Unknown Player';
        }

        if ($this->target_type === 'team') {
            return $this->team?->name ?? 'Unknown Team';
        }

        return $this->target_name ?: ucfirst((string) $this->target_type);
    }
}

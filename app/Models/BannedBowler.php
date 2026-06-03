<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannedBowler extends Model
{
    protected $fillable = [
        'player_id', 'reason', 'banned_from', 'banned_until',
        'is_active', 'notes', 'issued_by',
    ];

    protected $casts = [
        'banned_from'  => 'date',
        'banned_until' => 'date',
        'is_active'    => 'boolean',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

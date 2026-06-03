<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerSuspension extends Model
{
    protected $fillable = [
        'player_id', 'fine_id', 'type', 'reason', 'start_date',
        'end_date', 'matches_suspended', 'is_active', 'issued_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function fine(): BelongsTo
    {
        return $this->belongsTo(Fine::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}

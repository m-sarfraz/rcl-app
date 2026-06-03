<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fine extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id', 'player_id', 'edition_id', 'match_id', 'violation_type', 'card_type',
        'description', 'amount', 'status', 'due_date', 'paid_date',
        'issued_by', 'admin_notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(CricketMatch::class, 'match_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('status', '=', 'unpaid');
    }
}

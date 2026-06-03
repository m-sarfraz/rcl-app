<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poll extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'edition_id', 'match_id', 'question', 'description',
        'starts_at', 'ends_at', 'is_active', 'allow_anonymous',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'allow_anonymous' => 'boolean',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(CricketMatch::class, 'match_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('display_order');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function getTotalVotesAttribute(): int
    {
        return $this->votes()->count();
    }

    public function isActive(): bool
    {
        return $this->is_active
            && now()->between($this->starts_at, $this->ends_at);
    }
}

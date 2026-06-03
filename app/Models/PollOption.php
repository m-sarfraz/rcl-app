<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollOption extends Model
{
    protected $fillable = ['poll_id', 'option_text', 'option_image', 'display_order'];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function getVoteCountAttribute(): int
    {
        return $this->votes()->count();
    }

    public function getVotePercentageAttribute(): float
    {
        $total = $this->poll->total_votes;
        if ($total === 0) return 0;
        return round(($this->vote_count / $total) * 100, 1);
    }
}

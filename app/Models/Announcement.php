<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'message', 'type', 'edition_id', 'is_ticker',
        'is_active', 'expires_at', 'created_by',
    ];

    protected $casts = [
        'is_ticker' => 'boolean',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function scopeTicker($query)
    {
        return $query->active()->where('is_ticker', true);
    }
}

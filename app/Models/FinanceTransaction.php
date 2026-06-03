<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'edition_id', 'type', 'category', 'description', 'amount',
        'team_id', 'match_id', 'reference_number', 'transaction_date',
        'recorded_by', 'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(CricketMatch::class, 'match_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}

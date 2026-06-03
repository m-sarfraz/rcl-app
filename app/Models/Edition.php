<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Edition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'edition_number', 'host_village', 'thumbnail', 'banner',
        'start_date', 'end_date', 'status', 'description', 'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'edition_teams')
            ->withPivot('group_number')
            ->withTimestamps();
    }

    public function matches(): HasMany
    {
        return $this->hasMany(CricketMatch::class);
    }

    public function polls(): HasMany
    {
        return $this->hasMany(Poll::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function finances(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class);
    }

    public function playerStats(): HasMany
    {
        return $this->hasMany(PlayerEditionStat::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '=', 'active');
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', '=', 1);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'village_name', 'short_code', 'logo', 'cover_photo',
        'primary_color', 'secondary_color', 'description', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', '=', 1);
    }

    public function editions(): BelongsToMany
    {
        return $this->belongsToMany(Edition::class, 'edition_teams')
            ->withPivot('group_number')
            ->withTimestamps();
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'player_team_editions')
            ->withPivot('edition_id', 'is_captain', 'is_vice_captain', 'transfer_from_team', 'transfer_reason')
            ->withTimestamps();
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(CricketMatch::class, 'home_team_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(CricketMatch::class, 'away_team_id');
    }
}

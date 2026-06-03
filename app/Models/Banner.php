<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'image_path', 'link_url', 'is_active', 'display_order',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', '=', 1)->orderBy('display_order');
    }
}

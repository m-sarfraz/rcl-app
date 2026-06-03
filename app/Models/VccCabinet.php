<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VccCabinet extends Model
{
    use SoftDeletes;

    protected $table = 'vcc_cabinets';

    protected $fillable = [
        'name', 'role_title', 'bio', 'photo', 'village',
        'phone', 'display_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', '=', 1)->orderBy('display_order');
    }
}

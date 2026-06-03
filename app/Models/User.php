<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'google_id', 'avatar', 'phone',
        'admin_hash', 'is_active', 'role_id',
    ];

    protected $hidden = ['password', 'remember_token', 'admin_hash', 'google_id'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role?->name === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role_id !== null;
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->role?->hasPermission($permission) ?? false;
    }

    public function hasRole(string $role): bool
    {
        return $this->role?->name === $role;
    }
}

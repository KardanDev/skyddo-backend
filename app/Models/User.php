<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_SUPER_USER = 'super_user';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_MEMBER = 'member';

    public const ROLES = [
        self::ROLE_SUPER_USER,
        self::ROLE_ADMIN,
        self::ROLE_MEMBER,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_photo_path',
        'phone',
        'position',
        'bio',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the full URL for the user's profile photo
     */
    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profile_photo_path ? asset('storage/'.$this->profile_photo_path) : null;
    }

    /**
     * Get user initials for avatar fallback
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);

        return count($words) >= 2
            ? strtoupper(substr($words[0], 0, 1).substr($words[1], 0, 1))
            : strtoupper(substr($this->name, 0, 2));
    }

    public function isSuperUser(): bool
    {
        return $this->role === self::ROLE_SUPER_USER;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isMember(): bool
    {
        return $this->role === self::ROLE_MEMBER;
    }

    public function hasRole(string|array $roles): bool
    {
        $roles = (array) $roles;

        return in_array($this->role, $roles, true);
    }

    public function isAtLeast(string $role): bool
    {
        $hierarchy = [
            self::ROLE_SUPER_USER => 3,
            self::ROLE_ADMIN => 2,
            self::ROLE_MEMBER => 1,
        ];

        return ($hierarchy[$this->role] ?? 0) >= ($hierarchy[$role] ?? 0);
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class)
            ->withPivot('role')
            ->withTimestamps();
    }
}

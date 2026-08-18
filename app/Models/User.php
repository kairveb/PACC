<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'mfa_secret', 'mfa_enabled', 'last_activity_at'])]
#[Hidden(['password', 'remember_token', 'mfa_secret'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The password complexity rule set applied across the app
     * (login, registration, password update, and seeding).
     */
    public const PASSWORD_RULE = [
        'required',
        'string',
        'min:8',
        'max:100',
        'regex:/[a-z]/',      // at least one lowercase letter
        'regex:/[A-Z]/',      // at least one uppercase letter
        'regex:/[0-9]/',      // at least one number
        'regex:/[@$!%*#?&]/', // at least one symbol
    ];

    /**
     * Whether the user has completed two-factor (MFA) enrollment.
     */
    public function hasMfaEnabled(): bool
    {
        return (bool) $this->mfa_enabled && !empty($this->mfa_secret);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

public function hasRole(string ...$roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', fn ($q) => $q->where('name', $permission))
            ->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function isPatient(): bool
    {
        return $this->hasRole('patient');
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function provider(): HasOne
    {
        return $this->hasOne(Provider::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
return [
            'email_verified_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'mfa_enabled' => 'boolean',
            'password' => 'hashed',
        ];
    }
}

<?php

namespace App\Models;

use App\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'department_id',
    'joining_date',
    'is_active',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'department_id' => 'integer',
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'joining_date' => 'date',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return match ($panel->getId()) {
            'admin' => $this->role === UserRole::Admin,
            'employee' => $this->role === UserRole::Employee,
            default => false,
        };
    }

    public function closedMonths(): HasMany
    {
        return $this->hasMany(MonthlyLoeClosure::class, 'closed_by_user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isEmployee(): bool
    {
        return $this->role === UserRole::Employee;
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function monthlyLoeReportActivities(): HasMany
    {
        return $this->hasMany(MonthlyLoeReportActivity::class);
    }

    public function monthlyLoeReports(): HasMany
    {
        return $this->hasMany(MonthlyLoeReport::class);
    }

    public function projectAssignments(): HasMany
    {
        return $this->hasMany(ProjectAssignment::class);
    }
}

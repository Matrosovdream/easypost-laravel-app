<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name', 'email', 'password', 'pin_hash', 'phone', 'avatar_s3_key',
    'locale', 'timezone', 'is_active', 'current_team_id', 'last_login_at',
    'freshdesk_contact_id',
])]
#[Hidden(['password', 'remember_token', 'pin_hash', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    protected function casts(): array
    {
        return [
            'email_verified_at'        => 'datetime',
            'password'                 => 'hashed',
            'two_factor_confirmed_at'  => 'datetime',
            'last_login_at'            => 'datetime',
            'is_active'                => 'boolean',
        ];
    }

    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot(['client_id', 'spending_cap_cents', 'daily_cap_cents',
                         'station_id', 'warehouse_id', 'status', 'joined_at', 'last_seen_at'])
            ->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot(['team_id', 'assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    public function rights(): array
    {
        if (!$this->relationLoaded('roles')) {
            $this->load('roles.rights');
        }
        return $this->roles
            ->flatMap(fn (Role $r) => $r->rights->pluck('right'))
            ->unique()
            ->values()
            ->all();
    }
}

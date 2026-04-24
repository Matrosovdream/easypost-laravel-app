<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Billable;

class Team extends Model
{
    use Billable, SoftDeletes;

    protected $fillable = [
        'name', 'logo_s3_key', 'plan', 'status', 'mode',
        'stripe_customer_id', 'stripe_subscription_id', 'trial_ends_at',
        'ep_user_id', 'default_currency', 'time_zone', 'settings', 'owner_id',
    ];

    protected function casts(): array
    {
        return [
            'settings'       => 'array',
            'trial_ends_at'  => 'datetime',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot(['client_id', 'spending_cap_cents', 'daily_cap_cents',
                         'station_id', 'warehouse_id', 'status', 'joined_at', 'last_seen_at'])
            ->withTimestamps();
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Cashier looks up the customer ID under this column by default. Our teams table
     * already uses `stripe_customer_id`, so Cashier's default works out of the box;
     * this getter is just here to keep the override explicit.
     */
    public function stripeName(): ?string
    {
        return $this->name;
    }
}

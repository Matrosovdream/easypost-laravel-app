<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pickup extends Model
{
    protected $guarded = [];

    protected $casts = [
        'min_datetime' => 'datetime',
        'max_datetime' => 'datetime',
        'is_account_address' => 'bool',
        'rates_snapshot' => 'array',
        'cost_cents' => 'int',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

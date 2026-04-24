<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $guarded = [];

    protected $casts = [
        'residential' => 'bool',
        'verified' => 'bool',
        'verified_at' => 'datetime',
        'verification' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}

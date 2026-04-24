<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tracker extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'est_delivery_date' => 'datetime',
        'last_event_at' => 'datetime',
        'is_return' => 'bool',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(TrackerEvent::class)->orderBy('event_datetime', 'desc');
    }
}

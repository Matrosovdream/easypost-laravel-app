<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackerEvent extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'event_datetime' => 'datetime',
        'location' => 'array',
    ];

    public function tracker(): BelongsTo
    {
        return $this->belongsTo(Tracker::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Parcel extends Model
{
    protected $guarded = [];

    protected $casts = [
        'length_in' => 'float',
        'width_in' => 'float',
        'height_in' => 'float',
        'weight_oz' => 'float',
        'line_items' => 'array',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}

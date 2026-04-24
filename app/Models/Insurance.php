<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Insurance extends Model
{
    protected $table = 'insurances';

    protected $guarded = [];

    protected $casts = [
        'messages' => 'array',
        'amount_cents' => 'int',
        'fee_cents' => 'int',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanForm extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tracking_codes' => 'array',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function fromAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'from_address_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

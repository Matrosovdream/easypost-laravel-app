<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Batch extends Model
{
    protected $guarded = [];

    protected $casts = [
        'status_summary' => 'array',
        'num_shipments' => 'int',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function shipments(): BelongsToMany
    {
        return $this->belongsToMany(Shipment::class, 'batch_shipment')
            ->withPivot(['batch_status', 'batch_message'])
            ->withTimestamps();
    }

    public function scanForm(): BelongsTo
    {
        return $this->belongsTo(ScanForm::class, 'scan_form_id');
    }

    public function pickup(): BelongsTo
    {
        return $this->belongsTo(Pickup::class, 'pickup_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

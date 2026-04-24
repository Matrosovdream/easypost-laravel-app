<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturnRequest extends Model
{
    use SoftDeletes;

    protected $table = 'returns';

    protected $guarded = [];

    protected $casts = [
        'items' => 'array',
        'auto_refund' => 'bool',
        'approved_at' => 'datetime',
        'refunded_at' => 'datetime',
        'refund_amount_cents' => 'int',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function originalShipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'original_shipment_id');
    }

    public function returnShipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'return_shipment_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

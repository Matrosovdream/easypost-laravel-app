<?php

namespace App\Models;

use App\Models\Scopes\ShipmentTeamScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'selected_rate' => 'array',
        'rates_snapshot' => 'array',
        'options' => 'array',
        'forms' => 'array',
        'fees' => 'array',
        'messages' => 'array',
        'is_return' => 'bool',
        'approved_at' => 'datetime',
        'packed_at' => 'datetime',
        'refund_submitted_at' => 'datetime',
        'refunded_at' => 'datetime',
        'cost_cents' => 'int',
        'insurance_cents' => 'int',
        'declared_value_cents' => 'int',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ShipmentTeamScope());
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function toAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'to_address_id');
    }

    public function fromAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'from_address_id');
    }

    public function parcel(): BelongsTo
    {
        return $this->belongsTo(Parcel::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ShipmentEvent::class)->orderBy('created_at', 'desc');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }
}

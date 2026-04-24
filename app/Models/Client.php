<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id', 'company_name', 'contact_name', 'contact_email', 'contact_phone',
        'default_from_address_id', 'default_carrier_account_ids', 'flexrate_markup_pct',
        'per_service_markups', 'billing_mode', 'credit_terms_days', 'ep_endshipper_id',
        'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'default_carrier_account_ids' => 'array',
            'per_service_markups'         => 'array',
            'flexrate_markup_pct'         => 'float',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}

<?php

namespace App\Helpers\Clients;

use App\Models\Client;
use Illuminate\Support\Collection;

class ClientHelper
{
    public function toListItem(Client $c): array
    {
        return [
            'id' => $c->id,
            'company_name' => $c->company_name,
            'contact_name' => $c->contact_name,
            'contact_email' => $c->contact_email,
            'contact_phone' => $c->contact_phone,
            'flexrate_markup_pct' => (float) $c->flexrate_markup_pct,
            'per_service_markups' => $c->per_service_markups,
            'billing_mode' => $c->billing_mode,
            'credit_terms_days' => $c->credit_terms_days,
            'status' => $c->status,
            'ep_endshipper_id' => $c->ep_endshipper_id,
            'notes' => $c->notes,
            'created_at' => $c->created_at?->toIso8601String(),
        ];
    }

    public function toDetail(Client $c): array
    {
        return $this->toListItem($c);
    }

    public function toListPayload(Collection $rows): array
    {
        return [
            'data' => $rows->map(fn (Client $c) => $this->toListItem($c))->values(),
        ];
    }
}

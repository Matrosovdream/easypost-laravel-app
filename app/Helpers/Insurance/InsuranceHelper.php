<?php

namespace App\Helpers\Insurance;

use App\Models\Insurance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InsuranceHelper
{
    public function toListItem(Insurance $i): array
    {
        return [
            'id' => $i->id,
            'tracking_code' => $i->tracking_code,
            'carrier' => $i->carrier,
            'amount_cents' => $i->amount_cents,
            'fee_cents' => $i->fee_cents,
            'provider' => $i->provider,
            'status' => $i->status,
            'reference' => $i->reference,
            'shipment_id' => $i->shipment_id,
            'created_at' => $i->created_at?->toIso8601String(),
        ];
    }

    public function toCreatedPayload(Insurance $i): array
    {
        return [
            'id' => $i->id,
            'status' => $i->status,
            'ep_insurance_id' => $i->ep_insurance_id,
            'messages' => $i->messages,
        ];
    }

    public function toListPayload(LengthAwarePaginator $page): array
    {
        return [
            'data' => collect($page->items())->map(fn (Insurance $i) => $this->toListItem($i))->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ];
    }
}

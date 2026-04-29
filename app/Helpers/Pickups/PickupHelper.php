<?php

namespace App\Helpers\Pickups;

use App\Models\Pickup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PickupHelper
{
    public function toListItem(Pickup $p): array
    {
        return [
            'id' => $p->id,
            'reference' => $p->reference,
            'status' => $p->status,
            'carrier' => $p->carrier,
            'service' => $p->service,
            'confirmation' => $p->confirmation,
            'min_datetime' => $p->min_datetime?->toIso8601String(),
            'max_datetime' => $p->max_datetime?->toIso8601String(),
            'cost_cents' => $p->cost_cents,
            'address' => $p->address ? [
                'name' => $p->address->name,
                'city' => $p->address->city,
                'state' => $p->address->state,
            ] : null,
        ];
    }

    public function toDetail(Pickup $p): array
    {
        return [
            'id' => $p->id,
            'reference' => $p->reference,
            'status' => $p->status,
            'carrier' => $p->carrier,
            'service' => $p->service,
            'confirmation' => $p->confirmation,
            'min_datetime' => $p->min_datetime?->toIso8601String(),
            'max_datetime' => $p->max_datetime?->toIso8601String(),
            'cost_cents' => $p->cost_cents,
            'instructions' => $p->instructions,
            'rates' => $p->rates_snapshot,
            'address' => $p->address,
        ];
    }

    public function toScheduledPayload(Pickup $p): array
    {
        return [
            'id' => $p->id,
            'status' => $p->status,
            'rates' => $p->rates_snapshot,
        ];
    }

    public function toBuyResult(Pickup $p): array
    {
        return [
            'id' => $p->id,
            'status' => $p->status,
            'confirmation' => $p->confirmation,
        ];
    }

    public function toCancelResult(Pickup $p): array
    {
        return ['id' => $p->id, 'status' => $p->status];
    }

    public function toListPayload(LengthAwarePaginator $page): array
    {
        return [
            'data' => collect($page->items())->map(fn (Pickup $p) => $this->toListItem($p))->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ];
    }
}

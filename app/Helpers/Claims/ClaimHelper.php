<?php

namespace App\Helpers\Claims;

use App\Models\Claim;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClaimHelper
{
    public function toListItem(Claim $c): array
    {
        return [
            'id' => $c->id,
            'state' => $c->state,
            'type' => $c->type,
            'amount_cents' => $c->amount_cents,
            'recovered_cents' => $c->recovered_cents,
            'shipment_id' => $c->shipment_id,
            'shipment' => $c->shipment ? [
                'id' => $c->shipment->id,
                'reference' => $c->shipment->reference,
                'tracking_code' => $c->shipment->tracking_code,
            ] : null,
            'assignee' => $c->assignee ? ['id' => $c->assignee->id, 'name' => $c->assignee->name] : null,
            'approver' => $c->approver ? ['id' => $c->approver->id, 'name' => $c->approver->name] : null,
            'paid_at' => $c->paid_at?->toIso8601String(),
            'closed_at' => $c->closed_at?->toIso8601String(),
            'created_at' => $c->created_at?->toIso8601String(),
        ];
    }

    public function toDetail(Claim $c): array
    {
        return array_merge($this->toListItem($c), [
            'description' => $c->description,
            'timeline' => $c->timeline,
            'ep_claim_id' => $c->ep_claim_id,
        ]);
    }

    public function toIdentity(Claim $c): array
    {
        return ['id' => $c->id, 'state' => $c->state];
    }

    public function toApprovedResult(Claim $c): array
    {
        return [
            'id' => $c->id,
            'state' => $c->state,
            'recovered_cents' => $c->recovered_cents,
        ];
    }

    public function toListPayload(LengthAwarePaginator $page): array
    {
        return [
            'data' => collect($page->items())->map(fn (Claim $c) => $this->toListItem($c))->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ];
    }
}

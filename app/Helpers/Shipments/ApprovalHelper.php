<?php

namespace App\Helpers\Shipments;

use App\Models\Approval;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApprovalHelper
{
    public function toListItem(Approval $a): array
    {
        return [
            'id' => $a->id,
            'status' => $a->status,
            'cost_cents' => $a->cost_cents,
            'reason' => $a->reason,
            'note' => $a->note,
            'rate_snapshot' => $a->rate_snapshot,
            'requested_by' => $a->requester ? ['id' => $a->requester->id, 'name' => $a->requester->name] : null,
            'approver' => $a->approver ? ['id' => $a->approver->id, 'name' => $a->approver->name] : null,
            'shipment_id' => $a->shipment_id,
            'shipment' => $a->shipment ? [
                'id' => $a->shipment->id,
                'reference' => $a->shipment->reference,
                'status' => $a->shipment->status,
                'to_address' => $a->shipment->toAddress ? [
                    'city' => $a->shipment->toAddress->city,
                    'state' => $a->shipment->toAddress->state,
                    'country' => $a->shipment->toAddress->country,
                ] : null,
            ] : null,
            'created_at' => $a->created_at?->toIso8601String(),
            'resolved_at' => $a->resolved_at?->toIso8601String(),
        ];
    }

    public function toListPayload(LengthAwarePaginator $page): array
    {
        return [
            'data' => collect($page->items())->map(fn (Approval $a) => $this->toListItem($a))->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ];
    }

    public function toApprovedResult(array $actionResult): array
    {
        return [
            'status' => 'approved',
            'buy_status' => $actionResult['buy']['status'] ?? null,
            'shipment_id' => $actionResult['shipment']->id,
        ];
    }

    public function toDeclinedResult(): array
    {
        return ['status' => 'declined'];
    }
}

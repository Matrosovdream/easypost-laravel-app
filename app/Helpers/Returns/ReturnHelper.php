<?php

namespace App\Helpers\Returns;

use App\Models\ReturnRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReturnHelper
{
    public function toListItem(ReturnRequest $r): array
    {
        return [
            'id' => $r->id,
            'status' => $r->status,
            'reason' => $r->reason,
            'original_shipment_id' => $r->original_shipment_id,
            'return_shipment_id' => $r->return_shipment_id,
            'auto_refund' => $r->auto_refund,
            'created_by' => $r->creator ? ['id' => $r->creator->id, 'name' => $r->creator->name] : null,
            'created_at' => $r->created_at?->toIso8601String(),
        ];
    }

    public function toDetail(ReturnRequest $r): array
    {
        return array_merge($this->toListItem($r), [
            'items' => $r->items,
            'notes' => $r->notes,
            'approved_by' => $r->approver ? ['id' => $r->approver->id, 'name' => $r->approver->name] : null,
            'approved_at' => $r->approved_at?->toIso8601String(),
            'original_shipment' => $r->originalShipment ? [
                'id' => $r->originalShipment->id,
                'reference' => $r->originalShipment->reference,
                'tracking_code' => $r->originalShipment->tracking_code,
            ] : null,
            'return_shipment' => $r->returnShipment ? [
                'id' => $r->returnShipment->id,
                'reference' => $r->returnShipment->reference,
                'status' => $r->returnShipment->status,
                'tracking_code' => $r->returnShipment->tracking_code,
            ] : null,
        ]);
    }

    public function toIdentity(ReturnRequest $r): array
    {
        return ['id' => $r->id, 'status' => $r->status];
    }

    public function toApprovedResult(ReturnRequest $r): array
    {
        return [
            'id' => $r->id,
            'status' => $r->status,
            'return_shipment_id' => $r->return_shipment_id,
        ];
    }

    public function toListPayload(LengthAwarePaginator $page): array
    {
        return [
            'data' => collect($page->items())->map(fn (ReturnRequest $r) => $this->toListItem($r))->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ];
    }
}

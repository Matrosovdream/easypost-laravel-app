<?php

namespace App\Helpers\Batches;

use App\Models\Batch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BatchHelper
{
    /**
     * Compact shape for `index` rows.
     */
    public function toListItem(Batch $b): array
    {
        return [
            'id' => $b->id,
            'reference' => $b->reference,
            'state' => $b->state,
            'num_shipments' => $b->num_shipments,
            'label_url' => $b->label_pdf_s3_key,
            'created_by' => $b->creator ? ['id' => $b->creator->id, 'name' => $b->creator->name] : null,
            'created_at' => $b->created_at?->toIso8601String(),
        ];
    }

    /**
     * Full shape for `show`, including embedded shipments.
     */
    public function toDetail(Batch $b): array
    {
        return [
            'id' => $b->id,
            'reference' => $b->reference,
            'state' => $b->state,
            'num_shipments' => $b->num_shipments,
            'label_url' => $b->label_pdf_s3_key,
            'status_summary' => $b->status_summary,
            'scan_form_id' => $b->scan_form_id,
            'pickup_id' => $b->pickup_id,
            'shipments' => $b->shipments->map(fn ($s) => [
                'id' => $s->id,
                'status' => $s->status,
                'carrier' => $s->carrier,
                'service' => $s->service,
                'tracking_code' => $s->tracking_code,
                'reference' => $s->reference,
                'batch_status' => $s->pivot->batch_status,
                'batch_message' => $s->pivot->batch_message,
                'to_address' => $s->toAddress ? [
                    'city' => $s->toAddress->city,
                    'state' => $s->toAddress->state,
                    'country' => $s->toAddress->country,
                ] : null,
            ]),
        ];
    }

    /**
     * Minimal `{id, state}` payload used by store/buy responses.
     */
    public function toIdentity(Batch $b): array
    {
        return ['id' => $b->id, 'state' => $b->state];
    }

    /**
     * `{id, label_url}` payload for generateLabels response.
     */
    public function toLabelResult(Batch $b): array
    {
        return ['id' => $b->id, 'label_url' => $b->label_pdf_s3_key];
    }

    /**
     * Paginated list payload — `{data, meta}` shape.
     */
    public function toListPayload(LengthAwarePaginator $page): array
    {
        return [
            'data' => collect($page->items())->map(fn (Batch $b) => $this->toListItem($b))->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ];
    }
}

<?php

namespace App\Helpers\Trackers;

use App\Models\Tracker;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TrackerHelper
{
    public function toListItem(Tracker $t): array
    {
        return [
            'id' => $t->id,
            'tracking_code' => $t->tracking_code,
            'carrier' => $t->carrier,
            'status' => $t->status,
            'status_detail' => $t->status_detail,
            'est_delivery_date' => $t->est_delivery_date?->toIso8601String(),
            'last_event_at' => $t->last_event_at?->toIso8601String(),
            'public_url' => $t->public_url,
            'shipment_id' => $t->shipment_id,
            'created_at' => $t->created_at?->toIso8601String(),
        ];
    }

    public function toDetail(Tracker $t): array
    {
        return array_merge($this->toListItem($t), [
            'events' => $t->events->map(fn ($e) => [
                'status' => $e->status,
                'status_detail' => $e->status_detail,
                'message' => $e->message,
                'source' => $e->source,
                'event_datetime' => $e->event_datetime?->toIso8601String(),
                'location' => $e->location,
            ])->values(),
        ]);
    }

    public function toListPayload(LengthAwarePaginator $page): array
    {
        return [
            'data' => collect($page->items())->map(fn (Tracker $t) => $this->toListItem($t))->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ];
    }
}

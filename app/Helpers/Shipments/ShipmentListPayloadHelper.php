<?php

namespace App\Helpers\Shipments;

use App\Http\Resources\Api\ShipmentListItemResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShipmentListPayloadHelper
{
    public function toListPayload(LengthAwarePaginator $page, bool $includePerPage = false): array
    {
        $meta = [
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
            'total' => $page->total(),
        ];
        if ($includePerPage) {
            $meta['per_page'] = $page->perPage();
        }

        return [
            'data' => ShipmentListItemResource::collection($page->items())->resolve(),
            'meta' => $meta,
        ];
    }
}

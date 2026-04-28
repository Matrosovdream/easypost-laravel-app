<?php

namespace App\Helpers\Ops;

use App\Models\Shipment;
use Illuminate\Support\Collection;

class PrintQueueHelper
{
    public function toListItem(Shipment $s): array
    {
        return [
            'id' => $s->id,
            'reference' => $s->reference,
            'tracking_code' => $s->tracking_code,
            'carrier' => $s->carrier,
            'service' => $s->service,
            'label_url' => $s->label_s3_key,
            'assigned_to' => $s->assigned_to,
            'to_address' => $s->toAddress ? [
                'name' => $s->toAddress->name,
                'city' => $s->toAddress->city,
                'state' => $s->toAddress->state,
                'country' => $s->toAddress->country,
            ] : null,
        ];
    }

    public function toListPayload(Collection $rows): array
    {
        return [
            'data' => $rows->map(fn (Shipment $s) => $this->toListItem($s))->values(),
        ];
    }
}

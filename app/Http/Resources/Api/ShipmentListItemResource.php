<?php

namespace App\Http\Resources\Api;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Shipment $resource
 */
class ShipmentListItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $m = $this->resource instanceof Shipment
            ? $this->resource
            : ($this->resource['Model'] ?? null);

        return [
            'id' => $m?->id,
            'status' => $m?->status,
            'carrier' => $m?->carrier,
            'service' => $m?->service,
            'tracking_code' => $m?->tracking_code,
            'cost_cents' => $m?->cost_cents,
            'reference' => $m?->reference,
            'client_id' => $m?->client_id,
            'assigned_to' => $m?->assigned_to,
            'requested_by' => $m?->requested_by,
            'to_address' => $m?->relationLoaded('toAddress') && $m->toAddress ? [
                'name' => $m->toAddress->name,
                'city' => $m->toAddress->city,
                'state' => $m->toAddress->state,
                'country' => $m->toAddress->country,
                'zip' => $m->toAddress->zip,
            ] : null,
            'created_at' => $m?->created_at?->toIso8601String(),
        ];
    }
}

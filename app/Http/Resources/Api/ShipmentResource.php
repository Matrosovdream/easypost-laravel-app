<?php

namespace App\Http\Resources\Api;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Shipment $resource
 */
class ShipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Shipment $m */
        $m = $this->resource;
        $m->loadMissing(['toAddress', 'fromAddress', 'parcel', 'client', 'assignee', 'requester', 'events']);

        return [
            'id' => $m->id,
            'status' => $m->status,
            'status_detail' => $m->status_detail,
            'reference' => $m->reference,
            'carrier' => $m->carrier,
            'service' => $m->service,
            'tracking_code' => $m->tracking_code,
            'cost_cents' => $m->cost_cents,
            'insurance_cents' => $m->insurance_cents,
            'declared_value_cents' => $m->declared_value_cents,
            'is_return' => (bool) $m->is_return,
            'client_id' => $m->client_id,
            'ep_shipment_id' => $m->ep_shipment_id,
            'label_url' => $m->label_s3_key,
            'to_address' => $m->toAddress,
            'from_address' => $m->fromAddress,
            'parcel' => $m->parcel,
            'rates' => $m->rates_snapshot ?? [],
            'selected_rate' => $m->selected_rate,
            'options' => $m->options,
            'messages' => $m->messages,
            'assigned_to' => $m->assignee ? [
                'id' => $m->assignee->id,
                'name' => $m->assignee->name,
            ] : null,
            'requested_by' => $m->requester ? [
                'id' => $m->requester->id,
                'name' => $m->requester->name,
            ] : null,
            'events' => $m->events->map(fn ($e) => [
                'type' => $e->type,
                'payload' => $e->payload,
                'created_at' => $e->created_at?->toIso8601String(),
            ]),
            'approved_at' => $m->approved_at?->toIso8601String(),
            'packed_at' => $m->packed_at?->toIso8601String(),
            'created_at' => $m->created_at?->toIso8601String(),
        ];
    }
}

<?php

namespace App\Http\Controllers\Api\Ops;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Repositories\Shipping\ShipmentRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrintQueueController extends Controller
{
    public function __construct(private readonly ShipmentRepo $shipments) {}

    public function __invoke(Request $request): JsonResponse
    {
        abort_unless(in_array('labels.print', $request->user()->rights(), true), 403);

        $rows = $this->shipments->printQueue();

        return response()->json([
            'data' => $rows->map(fn (Shipment $s) => [
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
            ])->values(),
        ]);
    }
}

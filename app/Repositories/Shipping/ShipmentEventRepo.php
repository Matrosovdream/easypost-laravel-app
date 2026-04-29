<?php

namespace App\Repositories\Shipping;

use App\Models\ShipmentEvent;
use App\Repositories\AbstractRepo;

class ShipmentEventRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new ShipmentEvent();
    }

    public function record(int $shipmentId, string $type, ?array $payload = null, ?int $userId = null): ShipmentEvent
    {
        return ShipmentEvent::create([
            'shipment_id' => $shipmentId,
            'type' => $type,
            'payload' => $payload,
            'created_by' => $userId,
        ]);
    }
}

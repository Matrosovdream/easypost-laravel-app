<?php

namespace App\Actions\Shipments;

use App\Models\Shipment;
use App\Models\User;
use App\Repositories\Shipping\ShipmentEventRepo;
use App\Repositories\Shipping\ShipmentRepo;
use RuntimeException;

class MarkPackedAction
{
    public function __construct(
        private readonly ShipmentRepo $shipments,
        private readonly ShipmentEventRepo $events,
    ) {}

    public function execute(User $user, Shipment $shipment): Shipment
    {
        if ($shipment->status !== 'purchased') {
            throw new RuntimeException("Shipment must be purchased before packing (was {$shipment->status}).");
        }

        $updated = $this->shipments->markPacked($shipment->id, $user->id);
        $this->events->record($shipment->id, 'packed', [], $user->id);
        return $updated;
    }
}

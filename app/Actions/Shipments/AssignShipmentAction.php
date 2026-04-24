<?php

namespace App\Actions\Shipments;

use App\Models\Shipment;
use App\Models\User;
use App\Repositories\Shipping\ShipmentEventRepo;
use App\Repositories\Shipping\ShipmentRepo;

class AssignShipmentAction
{
    public function __construct(
        private readonly ShipmentRepo $shipments,
        private readonly ShipmentEventRepo $events,
    ) {}

    public function execute(User $user, Shipment $shipment, ?int $assigneeId): Shipment
    {
        $updated = $this->shipments->assign($shipment->id, $assigneeId);
        $this->events->record($shipment->id, 'assigned', ['assigned_to' => $assigneeId], $user->id);
        return $updated;
    }
}

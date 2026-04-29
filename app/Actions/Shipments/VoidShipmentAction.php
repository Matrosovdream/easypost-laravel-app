<?php

namespace App\Actions\Shipments;

use App\Models\Shipment;
use App\Models\User;
use App\Repositories\Shipping\ShipmentEventRepo;
use App\Repositories\Shipping\ShipmentRepo;
use App\Services\Shipping\EasyPostService;
use RuntimeException;

class VoidShipmentAction
{
    public function __construct(
        private readonly ShipmentRepo $shipments,
        private readonly ShipmentEventRepo $events,
        private readonly EasyPostService $ep,
    ) {}

    public function execute(User $user, Shipment $shipment, ?string $reason = null): Shipment
    {
        if (! in_array($shipment->status, ['purchased', 'packed'], true)) {
            throw new RuntimeException("Cannot void a shipment in status '{$shipment->status}'.");
        }

        if ($shipment->ep_shipment_id) {
            try {
                $this->ep->refund($shipment->ep_shipment_id);
            } catch (\Throwable $e) {
                $this->events->record($shipment->id, 'void_ep_error', ['error' => $e->getMessage()], $user->id);
            }
        }

        $updated = $this->shipments->markRefundRequested($shipment->id);

        $this->events->record($shipment->id, 'voided', ['reason' => $reason], $user->id);

        return $updated;
    }
}

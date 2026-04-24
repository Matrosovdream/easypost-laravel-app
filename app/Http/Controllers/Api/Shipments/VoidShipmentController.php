<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Actions\Shipments\VoidShipmentAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ShipmentResource;
use App\Repositories\Shipping\ShipmentRepo;
use Illuminate\Http\Request;

class VoidShipmentController extends Controller
{
    public function __construct(
        private readonly VoidShipmentAction $action,
        private readonly ShipmentRepo $shipments,
    ) {}

    public function __invoke(Request $request, int $id): ShipmentResource
    {
        $shipment = $this->shipments->findUnscoped($id);
        abort_if(! $shipment, 404);
        $this->authorize('void', $shipment);

        $shipment = $this->action->execute(
            $request->user(),
            $shipment,
            $request->string('reason')->toString() ?: null,
        );

        return new ShipmentResource($shipment);
    }
}

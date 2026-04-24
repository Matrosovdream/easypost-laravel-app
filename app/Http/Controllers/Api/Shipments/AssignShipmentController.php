<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Actions\Shipments\AssignShipmentAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ShipmentResource;
use App\Repositories\Shipping\ShipmentRepo;
use Illuminate\Http\Request;

class AssignShipmentController extends Controller
{
    public function __construct(
        private readonly AssignShipmentAction $action,
        private readonly ShipmentRepo $shipments,
    ) {}

    public function __invoke(Request $request, int $id): ShipmentResource
    {
        $request->validate(['assignee_id' => ['nullable', 'integer', 'exists:users,id']]);

        $shipment = $this->shipments->findUnscoped($id);
        abort_if(! $shipment, 404);
        $this->authorize('assign', $shipment);

        $shipment = $this->action->execute(
            $request->user(),
            $shipment,
            $request->integer('assignee_id') ?: null,
        );

        return new ShipmentResource($shipment);
    }
}

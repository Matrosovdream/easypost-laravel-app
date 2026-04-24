<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Actions\Shipments\MarkPackedAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ShipmentResource;
use App\Repositories\Shipping\ShipmentRepo;
use Illuminate\Http\Request;

class MarkPackedController extends Controller
{
    public function __construct(
        private readonly MarkPackedAction $action,
        private readonly ShipmentRepo $shipments,
    ) {}

    public function __invoke(Request $request, int $id): ShipmentResource
    {
        $shipment = $this->shipments->findUnscoped($id);
        abort_if(! $shipment, 404);
        $this->authorize('pack', $shipment);

        return new ShipmentResource($this->action->execute($request->user(), $shipment));
    }
}

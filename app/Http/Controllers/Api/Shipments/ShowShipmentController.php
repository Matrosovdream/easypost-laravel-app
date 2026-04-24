<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ShipmentResource;
use App\Repositories\Shipping\ShipmentRepo;
use Illuminate\Http\Request;

class ShowShipmentController extends Controller
{
    public function __construct(private readonly ShipmentRepo $shipments) {}

    public function __invoke(Request $request, int $id): ShipmentResource
    {
        // Route model binding wouldn't apply the scope+authorize flow, so we resolve via repo.
        // View policy re-checks team scope + role-based narrowing before exposing the record.
        $shipment = $this->shipments->query()->findOrFail($id);
        $this->authorize('view', $shipment);
        return new ShipmentResource($shipment);
    }
}

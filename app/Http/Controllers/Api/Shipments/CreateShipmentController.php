<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Actions\Shipments\CreateShipmentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Shipments\CreateShipmentRequest;
use App\Http\Resources\Api\ShipmentResource;

class CreateShipmentController extends Controller
{
    public function __construct(private readonly CreateShipmentAction $action) {}

    public function __invoke(CreateShipmentRequest $request): ShipmentResource
    {
        $shipment = $this->action->execute($request->user(), $request->validated());
        return new ShipmentResource($shipment);
    }
}

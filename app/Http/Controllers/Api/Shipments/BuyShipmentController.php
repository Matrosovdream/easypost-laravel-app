<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Actions\Shipments\BuyShipmentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Shipments\BuyShipmentRequest;
use App\Http\Resources\Api\ShipmentResource;
use App\Repositories\Shipping\ShipmentRepo;
use Illuminate\Http\JsonResponse;

class BuyShipmentController extends Controller
{
    public function __construct(
        private readonly BuyShipmentAction $action,
        private readonly ShipmentRepo $shipments,
    ) {}

    public function __invoke(BuyShipmentRequest $request, int $id): JsonResponse
    {
        $shipment = $this->shipments->findUnscoped($id);
        abort_if(! $shipment, 404);
        $this->authorize('buy', $shipment);

        $result = $this->action->execute(
            $request->user(),
            $shipment,
            $request->string('rate_id')->toString(),
            $request->integer('insurance_cents') ?: null,
        );

        return response()->json([
            'status' => $result['status'],
            'shipment' => (new ShipmentResource($result['shipment']))->resolve(),
            'approval_id' => $result['approval']?->id ?? null,
        ]);
    }
}

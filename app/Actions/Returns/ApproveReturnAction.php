<?php

namespace App\Actions\Returns;

use App\Models\ReturnRequest;
use App\Models\User;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Care\ReturnRequestRepo;
use App\Repositories\Shipping\ParcelRepo;
use App\Repositories\Shipping\ShipmentEventRepo;
use App\Repositories\Shipping\ShipmentRepo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ApproveReturnAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly ReturnRequestRepo $returns,
        private readonly ShipmentRepo $shipments,
        private readonly ShipmentEventRepo $events,
        private readonly ParcelRepo $parcels,
    ) {}

    public function execute(User $user, ReturnRequest $return): ReturnRequest
    {
        if ($return->status !== 'requested') {
            throw new RuntimeException("Return is already {$return->status}.");
        }

        return DB::transaction(function () use ($user, $return) {
            $original = $return->originalShipment;
            if (! $original) {
                throw new RuntimeException('Original shipment missing.');
            }

            $parcel = $this->parcels->createForTeam($return->team_id, [
                'predefined_package' => $original->parcel?->predefined_package,
                'length_in' => $original->parcel?->length_in,
                'width_in' => $original->parcel?->width_in,
                'height_in' => $original->parcel?->height_in,
                'weight_oz' => $original->parcel?->weight_oz ?? 8,
            ]);

            $rates = [];
            $epId = null;
            try {
                $resp = $this->ep->createReturnShipment([
                    'from_address' => ['id' => $original->toAddress?->ep_address_id],
                    'to_address' => ['id' => $original->fromAddress?->ep_address_id],
                    'parcel' => ['id' => $parcel->ep_parcel_id],
                ]);
                $rates = $resp['rates'] ?? [];
                $epId = $resp['id'] ?? null;
            } catch (\Throwable) {
                // degrade gracefully; Shipment stays in 'rated' state locally
            }

            $returnShipment = $this->shipments->markRequested([
                'team_id' => $return->team_id,
                'client_id' => $return->client_id,
                'ep_shipment_id' => $epId,
                'reference' => 'RETURN-'.($return->id),
                'status' => 'rated',
                'to_address_id' => $original->from_address_id,
                'from_address_id' => $original->to_address_id,
                'parcel_id' => $parcel->id,
                'is_return' => true,
                'requested_by' => $user->id,
                'rates_snapshot' => $rates,
            ]);

            $this->events->record(
                $returnShipment->id,
                'return_created',
                ['return_request_id' => $return->id],
                $user->id,
            );

            return $this->returns->markApproved($return, $user->id, $returnShipment->id);
        });
    }
}

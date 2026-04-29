<?php

namespace App\Actions\Shipments;

use App\Models\Parcel;
use App\Models\Shipment;
use App\Models\User;
use App\Repositories\Address\AddressRepo;
use App\Repositories\Shipping\ParcelRepo;
use App\Repositories\Shipping\ShipmentEventRepo;
use App\Repositories\Shipping\ShipmentRepo;
use App\Services\Shipping\EasyPostService;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class CreateShipmentAction
{
    public function __construct(
        private readonly ShipmentRepo $shipments,
        private readonly AddressRepo $addresses,
        private readonly ParcelRepo $parcels,
        private readonly ShipmentEventRepo $events,
        private readonly EasyPostService $ep,
    ) {}

    public function execute(User $user, array $input): Shipment
    {
        $teamId = (int) $user->current_team_id;

        return DB::transaction(function () use ($user, $teamId, $input) {
            $toAddress = is_array($input['to_address'])
                ? $this->addresses->createForTeam($teamId, $input['to_address'])
                : ($this->addresses->findInTeam($teamId, (int) $input['to_address'])
                    ?? throw new RuntimeException('to_address not found for this team.'));

            $fromAddress = is_array($input['from_address'])
                ? $this->addresses->createForTeam($teamId, $input['from_address'])
                : ($this->addresses->findInTeam($teamId, (int) $input['from_address'])
                    ?? throw new RuntimeException('from_address not found for this team.'));

            $parcel = $this->parcels->createForTeam($teamId, $input['parcel']);

            $epResponse = null;
            $rates = [];
            $epShipmentId = null;
            try {
                $epResponse = $this->ep->createShipmentWithRates(
                    from: $this->toEp($fromAddress),
                    to: $this->toEp($toAddress),
                    parcel: $this->parcelEp($parcel),
                    options: $input['options'] ?? [],
                )->json();
                $rates = $epResponse['rates'] ?? [];
                $epShipmentId = $epResponse['id'] ?? null;
            } catch (Throwable) {
                // EP unavailable — still create the shipment locally in 'rated' state.
            }

            $shipment = $this->shipments->markRequested([
                'team_id' => $teamId,
                'client_id' => $input['client_id'] ?? null,
                'ep_shipment_id' => $epShipmentId,
                'status' => 'rated',
                'to_address_id' => $toAddress->id,
                'from_address_id' => $fromAddress->id,
                'parcel_id' => $parcel->id,
                'is_return' => (bool) ($input['is_return'] ?? false),
                'requested_by' => $user->id,
                'rates_snapshot' => $rates,
                'options' => $input['options'] ?? null,
                'reference' => $input['reference'] ?? null,
                'declared_value_cents' => $input['declared_value_cents'] ?? null,
            ]);

            $this->events->record($shipment->id, 'created', ['rates_count' => count($rates)], $user->id);

            return $shipment;
        });
    }

    private function toEp($address): array
    {
        return array_filter([
            'name' => $address->name,
            'company' => $address->company,
            'street1' => $address->street1,
            'street2' => $address->street2,
            'city' => $address->city,
            'state' => $address->state,
            'zip' => $address->zip,
            'country' => $address->country,
            'phone' => $address->phone,
            'email' => $address->email,
        ], fn ($v) => $v !== null && $v !== '');
    }

    private function parcelEp(Parcel $p): array
    {
        return array_filter([
            'predefined_package' => $p->predefined_package,
            'length' => $p->length_in,
            'width' => $p->width_in,
            'height' => $p->height_in,
            'weight' => $p->weight_oz,
        ], fn ($v) => $v !== null);
    }
}

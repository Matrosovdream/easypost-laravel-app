<?php

namespace App\Services\DummyData\Importers;

use App\Models\Address;
use App\Models\Parcel;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Services\DummyData\AssignmentPicker;
use App\Services\DummyData\DummyDataLoader;

class ShipmentImporter
{
    public function __construct(private DummyDataLoader $loader) {}

    public function import(AssignmentPicker $picker): int
    {
        $count = 0;
        $teamId = $picker->teamId();
        $clientId = $picker->client()?->id;

        $addressMap = Address::query()
            ->where('team_id', $teamId)
            ->whereNotNull('ep_address_id')
            ->pluck('id', 'ep_address_id')
            ->all();

        $parcelMap = Parcel::query()
            ->where('team_id', $teamId)
            ->whereNotNull('ep_parcel_id')
            ->pluck('id', 'ep_parcel_id')
            ->all();

        foreach ($this->loader->load('shipments.json') as $payload) {
            if (empty($payload['id'])) {
                continue;
            }

            $existing = Shipment::withoutGlobalScopes()
                ->where('team_id', $teamId)
                ->where('ep_shipment_id', $payload['id'])
                ->first();
            if ($existing) {
                continue;
            }

            $toEpId = $payload['to_address']['id'] ?? null;
            $fromEpId = $payload['from_address']['id'] ?? null;
            $parcelEpId = $payload['parcel']['id'] ?? null;

            $toId = $toEpId ? ($addressMap[$toEpId] ?? null) : null;
            $fromId = $fromEpId ? ($addressMap[$fromEpId] ?? null) : null;
            $parcelId = $parcelEpId ? ($parcelMap[$parcelEpId] ?? null) : null;

            if (!$toId || !$fromId || !$parcelId) {
                continue;
            }

            $status = $payload['status'] ?? 'rated';
            $isReturn = (bool) ($payload['is_return'] ?? false);
            $selectedRate = $payload['selected_rate'] ?? null;
            $costCents = $selectedRate && isset($selectedRate['rate'])
                ? (int) round(((float) $selectedRate['rate']) * 100)
                : null;
            $insuranceCents = isset($payload['insurance']) && $payload['insurance'] !== null
                ? (int) round(((float) $payload['insurance']) * 100)
                : null;

            $approverState = in_array($status, ['purchased', 'packed', 'delivered', 'in_transit'], true);
            $packerState = in_array($status, ['packed', 'delivered', 'in_transit'], true);

            $shipment = Shipment::withoutGlobalScopes()->create([
                'team_id' => $teamId,
                'client_id' => $clientId,
                'ep_shipment_id' => $payload['id'],
                'reference' => $payload['reference'] ?? null,
                'status' => $status,
                'to_address_id' => $toId,
                'from_address_id' => $fromId,
                'parcel_id' => $parcelId,
                'is_return' => $isReturn,
                'requested_by' => $picker->shipper() ?? $picker->admin(),
                'assigned_to' => $packerState ? $picker->shipper() : null,
                'approved_by' => $approverState ? $picker->admin() : null,
                'approved_at' => $approverState ? now()->subDays(2) : null,
                'packed_at' => $packerState ? now()->subDay() : null,
                'tracking_code' => $payload['tracking_code'] ?? null,
                'carrier' => $payload['carrier'] ?? null,
                'service' => $payload['service'] ?? null,
                'selected_rate' => $selectedRate,
                'rates_snapshot' => $payload['rates'] ?? null,
                'options' => $payload['options'] ?? null,
                'fees' => $payload['fees'] ?? null,
                'cost_cents' => $costCents,
                'insurance_cents' => $insuranceCents,
                'currency' => $payload['options']['currency'] ?? 'USD',
                'messages' => $payload['messages'] ?? null,
            ]);

            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'type' => 'created',
                'payload' => ['source' => 'dummy_data', 'rates_count' => count($payload['rates'] ?? [])],
                'created_by' => $picker->admin(),
            ]);

            if ($selectedRate) {
                ShipmentEvent::create([
                    'shipment_id' => $shipment->id,
                    'type' => 'purchased',
                    'payload' => ['rate_id' => $selectedRate['id'] ?? null, 'cost_cents' => $costCents],
                    'created_by' => $picker->admin(),
                ]);
            }

            $count++;
        }

        return $count;
    }
}

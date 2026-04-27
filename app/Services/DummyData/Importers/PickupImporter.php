<?php

namespace App\Services\DummyData\Importers;

use App\Models\Address;
use App\Models\Pickup;
use App\Services\DummyData\AssignmentPicker;
use App\Services\DummyData\DummyDataLoader;

class PickupImporter
{
    public function __construct(private DummyDataLoader $loader) {}

    public function import(AssignmentPicker $picker): int
    {
        $count = 0;
        $teamId = $picker->teamId();

        $addressMap = Address::query()
            ->where('team_id', $teamId)
            ->whereNotNull('ep_address_id')
            ->pluck('id', 'ep_address_id')
            ->all();

        foreach ($this->loader->load('pickups.json') as $payload) {
            if (empty($payload['id'])) {
                continue;
            }

            $existing = Pickup::query()
                ->where('team_id', $teamId)
                ->where('ep_pickup_id', $payload['id'])
                ->first();
            if ($existing) {
                continue;
            }

            $addrEpId = $payload['address']['id'] ?? null;
            $addressId = $addrEpId ? ($addressMap[$addrEpId] ?? null) : null;
            if (!$addressId) {
                continue;
            }

            $rate = $payload['rates'][0] ?? null;
            $costCents = $rate && isset($rate['rate'])
                ? (int) round(((float) $rate['rate']) * 100)
                : null;

            Pickup::create([
                'team_id' => $teamId,
                'ep_pickup_id' => $payload['id'],
                'reference' => $payload['reference'] ?? null,
                'address_id' => $addressId,
                'min_datetime' => $payload['min_datetime'],
                'max_datetime' => $payload['max_datetime'],
                'instructions' => $payload['instructions'] ?? null,
                'is_account_address' => (bool) ($payload['is_account_address'] ?? false),
                'carrier' => $payload['carrier'] ?? null,
                'service' => $payload['service'] ?? null,
                'confirmation' => $payload['confirmation'] ?? null,
                'cost_cents' => $costCents,
                'status' => $payload['status'] ?? 'unknown',
                'rates_snapshot' => $payload['rates'] ?? null,
                'created_by' => $picker->shipper() ?? $picker->admin(),
            ]);
            $count++;
        }

        return $count;
    }
}

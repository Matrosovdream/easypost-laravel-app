<?php

namespace App\Services\DummyData\Importers;

use App\Models\Address;
use App\Services\DummyData\AssignmentPicker;
use App\Services\DummyData\DummyDataLoader;

class AddressImporter
{
    public function __construct(private DummyDataLoader $loader) {}

    public function import(AssignmentPicker $picker): int
    {
        $count = 0;
        $teamId = $picker->teamId();
        $clientId = $picker->client()?->id;
        $createdBy = $picker->admin();

        foreach ($this->loader->load('addresses.json') as $payload) {
            if (empty($payload['id'])) {
                continue;
            }

            $existing = Address::query()
                ->where('team_id', $teamId)
                ->where('ep_address_id', $payload['id'])
                ->first();
            if ($existing) {
                continue;
            }

            $verifications = $payload['verifications'] ?? null;
            $deliveryOk = $verifications['delivery']['success'] ?? null;

            Address::create([
                'team_id' => $teamId,
                'client_id' => $clientId,
                'ep_address_id' => $payload['id'],
                'name' => $payload['name'] ?? null,
                'company' => $payload['company'] ?? null,
                'street1' => $payload['street1'],
                'street2' => $payload['street2'] ?? null,
                'city' => $payload['city'] ?? null,
                'state' => $payload['state'] ?? null,
                'zip' => $payload['zip'] ?? null,
                'country' => $payload['country'] ?? 'US',
                'phone' => $payload['phone'] ?? null,
                'email' => $payload['email'] ?? null,
                'residential' => $payload['residential'] ?? null,
                'verified' => (bool) ($payload['verified'] ?? $deliveryOk ?? false),
                'verified_at' => ($payload['verified'] ?? $deliveryOk) ? now() : null,
                'verification' => $verifications,
                'created_by' => $createdBy,
            ]);
            $count++;
        }

        return $count;
    }
}

<?php

namespace App\Services\DummyData\Importers;

use App\Models\Insurance;
use App\Models\Shipment;
use App\Services\DummyData\AssignmentPicker;
use App\Services\DummyData\DummyDataLoader;

class InsuranceImporter
{
    public function __construct(private DummyDataLoader $loader) {}

    public function import(AssignmentPicker $picker): int
    {
        $count = 0;
        $teamId = $picker->teamId();

        $shipmentMap = Shipment::withoutGlobalScopes()
            ->where('team_id', $teamId)
            ->whereNotNull('ep_shipment_id')
            ->pluck('id', 'ep_shipment_id')
            ->all();

        foreach ($this->loader->load('insurances.json') as $payload) {
            if (empty($payload['id'])) {
                continue;
            }

            $existing = Insurance::query()
                ->where('team_id', $teamId)
                ->where('ep_insurance_id', $payload['id'])
                ->first();
            if ($existing) {
                continue;
            }

            $shipEpId = $payload['shipment_id'] ?? null;
            $shipmentId = $shipEpId ? ($shipmentMap[$shipEpId] ?? null) : null;

            $amountCents = isset($payload['amount']) ? (int) round(((float) $payload['amount']) * 100) : 0;
            $feeCents = isset($payload['fee']) ? (int) round(((float) $payload['fee']) * 100) : null;

            Insurance::create([
                'team_id' => $teamId,
                'shipment_id' => $shipmentId,
                'ep_insurance_id' => $payload['id'],
                'provider' => $payload['provider'] ?? null,
                'tracking_code' => $payload['tracking_code'] ?? null,
                'carrier' => $payload['carrier'] ?? null,
                'amount_cents' => $amountCents,
                'fee_cents' => $feeCents,
                'currency' => $payload['currency'] ?? 'USD',
                'status' => $payload['status'] ?? 'new',
                'reference' => $payload['reference'] ?? null,
                'messages' => $payload['messages'] ?? null,
            ]);
            $count++;
        }

        return $count;
    }
}

<?php

namespace App\Services\DummyData\Importers;

use App\Models\Claim;
use App\Models\Insurance;
use App\Models\Shipment;
use App\Services\DummyData\AssignmentPicker;
use App\Services\DummyData\DummyDataLoader;

class ClaimImporter
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

        $insuranceMap = Insurance::query()
            ->where('team_id', $teamId)
            ->whereNotNull('ep_insurance_id')
            ->pluck('id', 'ep_insurance_id')
            ->all();

        foreach ($this->loader->load('claims.json') as $payload) {
            if (empty($payload['id'])) {
                continue;
            }

            $existing = Claim::query()
                ->where('team_id', $teamId)
                ->where('ep_claim_id', $payload['id'])
                ->first();
            if ($existing) {
                continue;
            }

            $shipEpId = $payload['shipment_id'] ?? null;
            $shipmentId = $shipEpId ? ($shipmentMap[$shipEpId] ?? null) : null;
            if (!$shipmentId) {
                continue;
            }

            $insEpId = $payload['insurance_id'] ?? null;
            $insuranceId = $insEpId ? ($insuranceMap[$insEpId] ?? null) : null;

            $amountCents = isset($payload['amount']) ? (int) round(((float) $payload['amount']) * 100) : 0;
            $recoveredCents = isset($payload['recovered_amount']) && $payload['recovered_amount'] !== null
                ? (int) round(((float) $payload['recovered_amount']) * 100)
                : null;

            $state = $payload['status'] ?? 'open';

            Claim::create([
                'team_id' => $teamId,
                'shipment_id' => $shipmentId,
                'insurance_id' => $insuranceId,
                'ep_claim_id' => $payload['id'],
                'type' => $payload['type'] ?? 'damage',
                'amount_cents' => $amountCents,
                'recovered_cents' => $recoveredCents,
                'currency' => $payload['currency'] ?? 'USD',
                'description' => $payload['description'] ?? '',
                'state' => $state,
                'timeline' => $payload['timeline'] ?? null,
                'assigned_to' => $picker->csAgent() ?? $picker->manager(),
                'approved_by' => in_array($state, ['paid', 'approved'], true) ? $picker->manager() : null,
                'paid_at' => $payload['paid_at'] ?? null,
                'closed_at' => $payload['closed_at'] ?? null,
                'close_reason' => $payload['close_reason'] ?? null,
            ]);
            $count++;
        }

        return $count;
    }
}

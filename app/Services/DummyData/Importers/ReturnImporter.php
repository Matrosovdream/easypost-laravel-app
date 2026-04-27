<?php

namespace App\Services\DummyData\Importers;

use App\Models\ReturnRequest;
use App\Models\Shipment;
use App\Services\DummyData\AssignmentPicker;
use App\Services\DummyData\DummyDataLoader;

class ReturnImporter
{
    public function __construct(private DummyDataLoader $loader) {}

    public function import(AssignmentPicker $picker): int
    {
        $count = 0;
        $teamId = $picker->teamId();
        $clientId = $picker->client()?->id;

        $shipmentMap = Shipment::withoutGlobalScopes()
            ->where('team_id', $teamId)
            ->whereNotNull('ep_shipment_id')
            ->pluck('id', 'ep_shipment_id')
            ->all();

        foreach ($this->loader->load('returns.json') as $payload) {
            if (empty($payload['id'])) {
                continue;
            }

            $origEpId = $payload['original_shipment_id'] ?? null;
            $originalId = $origEpId ? ($shipmentMap[$origEpId] ?? null) : null;
            if (!$originalId) {
                continue;
            }

            $existing = ReturnRequest::query()
                ->where('team_id', $teamId)
                ->where('original_shipment_id', $originalId)
                ->where('reason', $payload['reason'] ?? null)
                ->first();
            if ($existing) {
                continue;
            }

            $returnEpId = $payload['return_shipment_id'] ?? null;
            $returnId = $returnEpId ? ($shipmentMap[$returnEpId] ?? null) : null;

            $status = $payload['status'] ?? 'requested';
            $refundCents = isset($payload['refund_amount']) && $payload['refund_amount'] !== null
                ? (int) round(((float) $payload['refund_amount']) * 100)
                : null;

            ReturnRequest::create([
                'team_id' => $teamId,
                'client_id' => $clientId,
                'original_shipment_id' => $originalId,
                'return_shipment_id' => $returnId,
                'reason' => $payload['reason'] ?? null,
                'items' => $payload['items'] ?? null,
                'status' => $status,
                'approved_by' => in_array($status, ['approved', 'declined'], true) ? $picker->manager() : null,
                'approved_at' => in_array($status, ['approved', 'declined'], true) ? now()->subDay() : null,
                'auto_refund' => (bool) ($payload['auto_refund'] ?? false),
                'refund_status' => $payload['refund_status'] ?? null,
                'refund_amount_cents' => $refundCents,
                'notes' => $payload['notes'] ?? null,
                'created_by' => $picker->csAgent() ?? $picker->admin(),
            ]);
            $count++;
        }

        return $count;
    }
}

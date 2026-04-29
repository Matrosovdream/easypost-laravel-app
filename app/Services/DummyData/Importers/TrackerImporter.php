<?php

namespace App\Services\DummyData\Importers;

use App\Models\Shipment;
use App\Models\Tracker;
use App\Models\TrackerEvent;
use App\Services\DummyData\AssignmentPicker;
use App\Services\DummyData\DummyDataLoader;

class TrackerImporter
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

        foreach ($this->loader->load('trackers.json') as $payload) {
            if (empty($payload['id']) || empty($payload['tracking_code'])) {
                continue;
            }

            $existing = Tracker::query()
                ->where('team_id', $teamId)
                ->where('ep_tracker_id', $payload['id'])
                ->first();
            if ($existing) {
                continue;
            }

            $shipmentEpId = $payload['shipment_id'] ?? null;
            $shipmentId = $shipmentEpId ? ($shipmentMap[$shipmentEpId] ?? null) : null;

            $events = $payload['tracking_details'] ?? [];
            $lastEventAt = null;
            foreach ($events as $e) {
                if (!empty($e['datetime'])) {
                    $lastEventAt = $e['datetime'];
                }
            }

            $tracker = Tracker::create([
                'team_id' => $teamId,
                'shipment_id' => $shipmentId,
                'ep_tracker_id' => $payload['id'],
                'tracking_code' => $payload['tracking_code'],
                'carrier' => $payload['carrier'] ?? 'USPS',
                'status' => $payload['status'] ?? 'unknown',
                'status_detail' => $payload['status_detail'] ?? null,
                'est_delivery_date' => $payload['est_delivery_date'] ?? null,
                'public_url' => $payload['public_url'] ?? null,
                'signed_by' => $payload['signed_by'] ?? null,
                'weight_oz' => $payload['weight'] ?? null,
                'last_event_at' => $lastEventAt,
                'is_return' => (bool) ($payload['is_return'] ?? false),
            ]);

            foreach ($events as $e) {
                if (empty($e['datetime']) || empty($e['status'])) {
                    continue;
                }
                TrackerEvent::create([
                    'tracker_id' => $tracker->id,
                    'message' => $e['message'] ?? '',
                    'status' => $e['status'],
                    'status_detail' => $e['status_detail'] ?? null,
                    'source' => $e['source'] ?? null,
                    'event_datetime' => $e['datetime'],
                    'location' => $e['tracking_location'] ?? null,
                ]);
            }

            $count++;
        }

        return $count;
    }
}

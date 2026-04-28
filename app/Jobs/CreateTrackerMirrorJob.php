<?php

namespace App\Jobs;

use App\Models\Shipment;
use App\Models\Tracker;
use App\Models\TrackerEvent;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable as QueueableTrait;

/**
 * Seeds a local `trackers` row from EP once a shipment has been purchased so the
 * Shipment detail page + branded tracking URL can render without waiting for
 * the first webhook to fire.
 */
class CreateTrackerMirrorJob implements ShouldQueue
{
    use Queueable, QueueableTrait;

    public int $tries = 3;
    public int $backoff = 15;

    public function __construct(public int $shipmentId) {}

    public function handle(EasyPostClient $ep): void
    {
        $shipment = Shipment::withoutGlobalScopes()->find($this->shipmentId);
        if (! $shipment || ! $shipment->tracking_code || ! $shipment->carrier) return;

        // Skip if we already have a tracker for this code
        if (Tracker::where('tracking_code', $shipment->tracking_code)->exists()) return;

        try {
            $resp = $ep->createTracker($shipment->tracking_code, $shipment->carrier)->json();
        } catch (\Throwable) {
            return; // queue will retry
        }

        $tracker = Tracker::create([
            'team_id' => $shipment->team_id,
            'shipment_id' => $shipment->id,
            'ep_tracker_id' => $resp['id'] ?? ('trk_auto_'.uniqid()),
            'tracking_code' => $shipment->tracking_code,
            'carrier' => $shipment->carrier,
            'status' => $resp['status'] ?? 'pre_transit',
            'status_detail' => $resp['status_detail'] ?? null,
            'public_url' => $resp['public_url'] ?? null,
        ]);

        foreach ($resp['tracking_details'] ?? [] as $evt) {
            if (! isset($evt['datetime'])) continue;
            TrackerEvent::create([
                'tracker_id' => $tracker->id,
                'message' => substr((string) ($evt['message'] ?? ''), 0, 255),
                'status' => $evt['status'] ?? 'unknown',
                'status_detail' => $evt['status_detail'] ?? null,
                'source' => $evt['source'] ?? 'EasyPost',
                'event_datetime' => $evt['datetime'],
                'location' => $evt['tracking_location'] ?? null,
            ]);
        }
    }
}

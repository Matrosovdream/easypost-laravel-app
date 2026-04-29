<?php

namespace App\Actions\Webhooks;

use App\Events\CountsUpdated;
use App\Events\ShipmentUpdated;
use App\Events\TrackerUpdated;
use App\Jobs\CreateTrackerMirrorJob;
use App\Jobs\DownloadLabelAssetsJob;
use App\Jobs\SendTrackingNotificationJob;
use App\Models\Tracker;
use App\Repositories\Operations\BatchRepo;
use App\Repositories\Shipping\ShipmentRepo;
use App\Repositories\Tracker\TrackerEventRepo;
use App\Repositories\Tracker\TrackerRepo;

/**
 * Dispatches EasyPost webhook payloads to the matching handler. Every handler is
 * idempotent — replays from EP must not double-update state or double-fire jobs.
 *
 * All persistence goes through repos; this action never touches Eloquent models
 * directly.
 */
class ProcessEasypostWebhookAction
{
    public function __construct(
        private readonly ShipmentRepo $shipments,
        private readonly TrackerRepo $trackers,
        private readonly TrackerEventRepo $trackerEvents,
        private readonly BatchRepo $batches,
    ) {}

    public function execute(string $description, array $result, ?int $teamId): void
    {
        match (true) {
            str_starts_with($description, 'tracker.') => $this->handleTrackerUpdated($result, $teamId),
            str_starts_with($description, 'batch.')   => $this->handleBatchUpdated($result, $teamId),
            str_starts_with($description, 'shipment.') && (($result['status'] ?? null) === 'purchased')
                => $this->handleShipmentPurchased($result, $teamId),
            default => null,
        };
    }

    public function handleTrackerUpdated(array $result, ?int $teamId): void
    {
        $epId = (string) ($result['id'] ?? '');
        $code = (string) ($result['tracking_code'] ?? '');
        if (! $epId && ! $code) return;

        $tracker = $this->trackers->findByEpIdOrCode($epId ?: null, $code ?: null);

        if (! $tracker && $teamId) {
            /** @var Tracker $tracker */
            $tracker = $this->trackers->create([
                'team_id' => $teamId,
                'ep_tracker_id' => $epId ?: ('trk_auto_'.uniqid()),
                'tracking_code' => $code,
                'carrier' => (string) ($result['carrier'] ?? 'UNKNOWN'),
                'status' => (string) ($result['status'] ?? 'unknown'),
            ])['Model'];
        }
        if (! $tracker) return;

        $this->trackers->updateStatus($tracker, [
            'status' => (string) ($result['status'] ?? $tracker->status),
            'status_detail' => $result['status_detail'] ?? $tracker->status_detail,
            'est_delivery_date' => $result['est_delivery_date'] ?? $tracker->est_delivery_date,
            'public_url' => $result['public_url'] ?? $tracker->public_url,
            'last_event_at' => now(),
        ]);

        foreach ($result['tracking_details'] ?? [] as $evt) {
            $at = $evt['datetime'] ?? null;
            if (! $at) continue;
            if ($this->trackerEvents->hasEvent($tracker->id, $at, (string) ($evt['status'] ?? ''))) continue;

            $this->trackerEvents->record($tracker->id, [
                'message' => substr((string) ($evt['message'] ?? ''), 0, 255),
                'status' => (string) ($evt['status'] ?? 'unknown'),
                'status_detail' => $evt['status_detail'] ?? null,
                'source' => $evt['source'] ?? 'EasyPost',
                'event_datetime' => $at,
                'location' => $evt['tracking_location'] ?? null,
            ]);
        }

        // Mirror status onto the shipment (if any) and broadcast
        if ($code) {
            $shipment = $this->shipments->findByTrackingCode($code);
            if ($shipment) {
                if ($shipment->status !== 'delivered' && ($result['status'] ?? null)) {
                    $this->shipments->updateStatus($shipment, [
                        'status' => $this->mirrorShipmentStatus($shipment->status, (string) $result['status']),
                        'status_detail' => $result['status_detail'] ?? $shipment->status_detail,
                    ]);
                    event(new ShipmentUpdated($shipment->fresh()));
                }
                SendTrackingNotificationJob::dispatch($shipment->id, (string) ($result['status'] ?? ''));
            }
        }

        event(new TrackerUpdated($tracker->fresh()));
        if ($teamId) event(new CountsUpdated($teamId));
    }

    public function handleBatchUpdated(array $result, ?int $teamId): void
    {
        $epId = (string) ($result['id'] ?? '');
        if (! $epId) return;
        $batch = $this->batches->getModel()->newQuery()->where('ep_batch_id', $epId)->first();
        if (! $batch) return;

        $this->batches->updateState(
            $batch,
            (string) ($result['state'] ?? $batch->state),
            $result['status'] ?? null,
            $result['label_url'] ?? null,
        );
    }

    public function handleShipmentPurchased(array $result, ?int $teamId): void
    {
        $epId = (string) ($result['id'] ?? '');
        if (! $epId) return;

        $shipment = $this->shipments->findByEpShipmentId($epId);
        if (! $shipment) return;

        if ($shipment->status !== 'purchased') {
            $this->shipments->updateStatus($shipment, [
                'status' => 'purchased',
                'tracking_code' => $result['tracking_code'] ?? $shipment->tracking_code,
                'label_s3_key' => $result['postage_label']['label_url'] ?? $shipment->label_s3_key,
            ]);
            event(new ShipmentUpdated($shipment->fresh()));
        }

        DownloadLabelAssetsJob::dispatch($shipment->id);
        if ($shipment->tracking_code && $shipment->carrier) {
            CreateTrackerMirrorJob::dispatch($shipment->id);
        }
    }

    private function mirrorShipmentStatus(string $current, string $trackerStatus): string
    {
        $order = ['requested', 'rated', 'pending_approval', 'purchased', 'packed', 'in_transit', 'out_for_delivery', 'delivered'];
        $currentIdx = array_search($current, $order, true);
        $nextIdx = array_search($trackerStatus, $order, true);
        return ($nextIdx !== false && ($currentIdx === false || $nextIdx > $currentIdx)) ? $trackerStatus : $current;
    }
}

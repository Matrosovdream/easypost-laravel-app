<?php

namespace App\Actions\Trackers;

use App\Models\Tracker;
use App\Models\User;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Tracker\TrackerEventRepo;
use App\Repositories\Tracker\TrackerRepo;
use Illuminate\Support\Facades\DB;

class CreateStandaloneTrackerAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly TrackerRepo $trackers,
        private readonly TrackerEventRepo $events,
    ) {}

    public function execute(User $user, string $trackingCode, string $carrier): Tracker
    {
        $teamId = (int) $user->current_team_id;

        $ep = null;
        try {
            $ep = $this->ep->createTracker($trackingCode, $carrier);
        } catch (\Throwable) {
            // degrade gracefully
        }

        return DB::transaction(function () use ($teamId, $trackingCode, $carrier, $ep) {
            /** @var Tracker $tracker */
            $tracker = $this->trackers->create([
                'team_id' => $teamId,
                'ep_tracker_id' => $ep['id'] ?? ('trk_local_'.uniqid()),
                'tracking_code' => $trackingCode,
                'carrier' => $carrier,
                'status' => $ep['status'] ?? 'pre_transit',
                'status_detail' => $ep['status_detail'] ?? null,
                'public_url' => $ep['public_url'] ?? null,
                'last_event_at' => isset($ep['updated_at']) ? now() : null,
            ])['Model'];

            foreach ($ep['tracking_details'] ?? [] as $evt) {
                $this->events->record($tracker->id, [
                    'message' => substr((string) ($evt['message'] ?? ''), 0, 255),
                    'status' => $evt['status'] ?? 'unknown',
                    'status_detail' => $evt['status_detail'] ?? null,
                    'source' => $evt['source'] ?? 'EasyPost',
                    'event_datetime' => $evt['datetime'] ?? now(),
                    'location' => $evt['tracking_location'] ?? null,
                ]);
            }

            return $tracker->fresh(['events']);
        });
    }
}

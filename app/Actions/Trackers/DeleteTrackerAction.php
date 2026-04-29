<?php

namespace App\Actions\Trackers;

use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Tracker\TrackerRepo;
use Illuminate\Support\Facades\Gate;

class DeleteTrackerAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly TrackerRepo $trackers,
    ) {}

    public function execute(int $id): array
    {
        $tracker = $this->trackers->findWithEvents($id);
        abort_if(! $tracker, 404);
        Gate::authorize('delete', $tracker);

        if ($tracker->ep_tracker_id && str_starts_with($tracker->ep_tracker_id, 'trk_')) {
            try {
                $this->ep->deleteTracker($tracker->ep_tracker_id);
            } catch (\Throwable) {
                // proceed with local delete anyway
            }
        }
        $this->trackers->delete($tracker->id);

        return ['ok' => true];
    }
}

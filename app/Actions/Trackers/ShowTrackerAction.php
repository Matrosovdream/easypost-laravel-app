<?php

namespace App\Actions\Trackers;

use App\Helpers\Trackers\TrackerHelper;
use App\Repositories\Tracker\TrackerRepo;
use Illuminate\Support\Facades\Gate;

class ShowTrackerAction
{
    public function __construct(
        private readonly TrackerRepo $trackers,
        private readonly TrackerHelper $helper,
    ) {}

    public function execute(int $id): array
    {
        $tracker = $this->trackers->findWithEvents($id);
        abort_if(! $tracker, 404);
        Gate::authorize('view', $tracker);

        return $this->helper->toDetail($tracker);
    }
}

<?php

namespace App\Actions\Trackers;

use App\Helpers\Trackers\TrackerHelper;
use App\Models\Tracker;
use App\Models\User;
use App\Repositories\Tracker\TrackerRepo;
use Illuminate\Support\Facades\Gate;

class ListTrackersAction
{
    public function __construct(
        private readonly TrackerRepo $trackers,
        private readonly TrackerHelper $helper,
    ) {}

    public function execute(User $user, ?string $status = null, ?string $carrier = null, int $perPage = 25): array
    {
        Gate::authorize('viewAny', Tracker::class);

        $page = $this->trackers->paginateForTeam(
            teamId: (int) $user->current_team_id,
            status: $status,
            carrier: $carrier,
            perPage: $perPage,
        );

        return $this->helper->toListPayload($page);
    }
}

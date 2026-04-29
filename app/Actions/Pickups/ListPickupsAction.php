<?php

namespace App\Actions\Pickups;

use App\Helpers\Pickups\PickupHelper;
use App\Models\Pickup;
use App\Models\User;
use App\Repositories\Operations\PickupRepo;
use Illuminate\Support\Facades\Gate;

class ListPickupsAction
{
    public function __construct(
        private readonly PickupRepo $pickups,
        private readonly PickupHelper $helper,
    ) {}

    public function execute(User $user, ?string $status = null, int $perPage = 25): array
    {
        Gate::authorize('viewAny', Pickup::class);

        $page = $this->pickups->paginateForTeam(
            teamId: (int) $user->current_team_id,
            status: $status,
            perPage: $perPage,
        );

        return $this->helper->toListPayload($page);
    }
}

<?php

namespace App\Actions\Batches;

use App\Helpers\Batches\BatchHelper;
use App\Models\Batch;
use App\Models\User;
use App\Repositories\Operations\BatchRepo;
use Illuminate\Support\Facades\Gate;

class ListBatchesAction
{
    public function __construct(
        private readonly BatchRepo $batches,
        private readonly BatchHelper $helper,
    ) {}

    public function execute(User $user, ?string $state = null, int $perPage = 25): array
    {
        Gate::authorize('viewAny', Batch::class);

        $page = $this->batches->paginateForTeam(
            teamId: (int) $user->current_team_id,
            state: $state,
            perPage: $perPage,
        );

        return $this->helper->toListPayload($page);
    }
}

<?php

namespace App\Actions\Claims;

use App\Helpers\Claims\ClaimHelper;
use App\Models\Claim;
use App\Models\User;
use App\Repositories\Care\ClaimRepo;
use Illuminate\Support\Facades\Gate;

class ListClaimsAction
{
    public function __construct(
        private readonly ClaimRepo $claims,
        private readonly ClaimHelper $helper,
    ) {}

    public function execute(User $user, ?string $state = null, int $perPage = 25): array
    {
        Gate::authorize('viewAny', Claim::class);

        $page = $this->claims->paginateForTeam(
            teamId: (int) $user->current_team_id,
            state: $state,
            perPage: $perPage,
        );

        return $this->helper->toListPayload($page);
    }
}

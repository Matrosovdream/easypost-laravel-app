<?php

namespace App\Actions\Returns;

use App\Helpers\Returns\ReturnHelper;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Repositories\Care\ReturnRequestRepo;
use App\Repositories\User\UserRepo;
use Illuminate\Support\Facades\Gate;

class ListReturnsAction
{
    public function __construct(
        private readonly ReturnRequestRepo $returns,
        private readonly UserRepo $users,
        private readonly ReturnHelper $helper,
    ) {}

    public function execute(User $user, ?string $status = null, int $perPage = 25): array
    {
        Gate::authorize('viewAny', ReturnRequest::class);

        // Client-scoped narrowing: restrict to their own client_id via team_user pivot.
        $clientScope = null;
        if (in_array('client', $user->roles->pluck('slug')->all(), true)
            && ! in_array('returns.view.any', $user->rights(), true)
        ) {
            $membership = $this->users->teamMembershipForUser($user->id, (int) $user->current_team_id);
            $clientScope = $membership?->client_id ?? 0; // 0 → no match
        }

        $page = $this->returns->paginateForTeam(
            teamId: (int) $user->current_team_id,
            clientScope: $clientScope,
            status: $status,
            perPage: $perPage,
        );

        return $this->helper->toListPayload($page);
    }
}

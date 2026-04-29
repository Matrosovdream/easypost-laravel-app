<?php

namespace App\Actions\Claims;

use App\Helpers\Claims\ClaimHelper;
use App\Models\User;
use App\Repositories\Care\ClaimRepo;
use Illuminate\Support\Facades\Gate;

class ShowClaimAction
{
    public function __construct(
        private readonly ClaimRepo $claims,
        private readonly ClaimHelper $helper,
    ) {}

    public function execute(User $user, int $id): array
    {
        $claim = $this->claims->findInTeam((int) $user->current_team_id, $id);
        abort_if(! $claim, 404);
        Gate::authorize('view', $claim);

        return $this->helper->toDetail($claim);
    }
}

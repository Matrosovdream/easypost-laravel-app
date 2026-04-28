<?php

namespace App\Actions\Claims;

use App\Helpers\Claims\ClaimHelper;
use App\Models\User;
use App\Repositories\Care\ClaimRepo;
use Illuminate\Support\Facades\Gate;

class CloseClaimAction
{
    public function __construct(
        private readonly ClaimRepo $claims,
        private readonly ClaimHelper $helper,
    ) {}

    public function execute(User $user, int $id, ?string $reason = null): array
    {
        $claim = $this->claims->findInTeam((int) $user->current_team_id, $id);
        abort_if(! $claim, 404);
        Gate::authorize('view', $claim);

        $claim = $this->claims->transition(
            $claim,
            [
                'state' => 'closed',
                'close_reason' => $reason,
                'closed_at' => now(),
            ],
            ['at' => now()->toIso8601String(), 'event' => 'closed', 'by' => $user->id, 'reason' => $reason],
        );

        return $this->helper->toIdentity($claim);
    }
}

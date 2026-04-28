<?php

namespace App\Actions\Claims;

use App\Helpers\Claims\ClaimHelper;
use App\Models\User;
use App\Repositories\Care\ClaimRepo;
use RuntimeException;

class ApproveClaimAction
{
    public function __construct(
        private readonly ClaimRepo $claims,
        private readonly ClaimHelper $helper,
    ) {}

    public function execute(User $user, int $id, ?int $recoveredCents = null): array
    {
        $claim = $this->claims->findInTeam((int) $user->current_team_id, $id);
        abort_if(! $claim, 404);
        abort_unless($user->can('approve', $claim), 403);

        if (! in_array($claim->state, ['submitted', 'open'], true)) {
            throw new RuntimeException("Claim in state '{$claim->state}' cannot be approved.");
        }

        $claim = $this->claims->transition(
            $claim,
            [
                'state' => 'approved',
                'approved_by' => $user->id,
                'recovered_cents' => $recoveredCents ?? $claim->amount_cents,
            ],
            ['at' => now()->toIso8601String(), 'event' => 'approved', 'by' => $user->id],
        );

        return $this->helper->toApprovedResult($claim);
    }
}

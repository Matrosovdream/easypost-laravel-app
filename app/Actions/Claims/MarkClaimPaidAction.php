<?php

namespace App\Actions\Claims;

use App\Helpers\Claims\ClaimHelper;
use App\Models\User;
use App\Repositories\Care\ClaimRepo;
use RuntimeException;

class MarkClaimPaidAction
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

        if (! in_array($claim->state, ['approved', 'submitted'], true)) {
            throw new RuntimeException("Claim in state '{$claim->state}' cannot be paid.");
        }

        $claim = $this->claims->transition(
            $claim,
            [
                'state' => 'paid',
                'recovered_cents' => $recoveredCents ?? $claim->recovered_cents ?? $claim->amount_cents,
                'paid_at' => now(),
            ],
            ['at' => now()->toIso8601String(), 'event' => 'paid', 'by' => $user->id],
        );

        return $this->helper->toIdentity($claim);
    }
}

<?php

namespace App\Actions\Claims;

use App\Models\Claim;
use App\Models\User;
use App\Repositories\Care\ClaimRepo;
use RuntimeException;

class MarkClaimPaidAction
{
    public function __construct(private readonly ClaimRepo $claims) {}

    public function execute(User $user, Claim $claim, ?int $recoveredCents = null): Claim
    {
        if (! in_array($claim->state, ['approved', 'submitted'], true)) {
            throw new RuntimeException("Claim in state '{$claim->state}' cannot be paid.");
        }

        return $this->claims->transition(
            $claim,
            [
                'state' => 'paid',
                'recovered_cents' => $recoveredCents ?? $claim->recovered_cents ?? $claim->amount_cents,
                'paid_at' => now(),
            ],
            ['at' => now()->toIso8601String(), 'event' => 'paid', 'by' => $user->id],
        );
    }
}

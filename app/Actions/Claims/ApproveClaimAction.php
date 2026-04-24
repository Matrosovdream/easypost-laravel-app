<?php

namespace App\Actions\Claims;

use App\Models\Claim;
use App\Models\User;
use App\Repositories\Care\ClaimRepo;
use RuntimeException;

class ApproveClaimAction
{
    public function __construct(private readonly ClaimRepo $claims) {}

    public function execute(User $user, Claim $claim, ?int $recoveredCents = null): Claim
    {
        if (! in_array($claim->state, ['submitted', 'open'], true)) {
            throw new RuntimeException("Claim in state '{$claim->state}' cannot be approved.");
        }

        return $this->claims->transition(
            $claim,
            [
                'state' => 'approved',
                'approved_by' => $user->id,
                'recovered_cents' => $recoveredCents ?? $claim->amount_cents,
            ],
            ['at' => now()->toIso8601String(), 'event' => 'approved', 'by' => $user->id],
        );
    }
}

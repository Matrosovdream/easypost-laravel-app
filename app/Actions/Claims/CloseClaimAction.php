<?php

namespace App\Actions\Claims;

use App\Models\Claim;
use App\Models\User;
use App\Repositories\Care\ClaimRepo;

class CloseClaimAction
{
    public function __construct(private readonly ClaimRepo $claims) {}

    public function execute(User $user, Claim $claim, ?string $reason = null): Claim
    {
        return $this->claims->transition(
            $claim,
            [
                'state' => 'closed',
                'close_reason' => $reason,
                'closed_at' => now(),
            ],
            ['at' => now()->toIso8601String(), 'event' => 'closed', 'by' => $user->id, 'reason' => $reason],
        );
    }
}

<?php

namespace App\Actions\Claims;

use App\Helpers\Claims\ClaimHelper;
use App\Models\User;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Care\ClaimRepo;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

class SubmitClaimAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly ClaimRepo $claims,
        private readonly ClaimHelper $helper,
    ) {}

    public function execute(User $user, int $id): array
    {
        $claim = $this->claims->findInTeam((int) $user->current_team_id, $id);
        abort_if(! $claim, 404);
        Gate::authorize('view', $claim);

        if ($claim->state !== 'open') {
            throw new RuntimeException("Claim already in state '{$claim->state}'.");
        }

        $epId = null;
        try {
            $resp = $this->ep->createClaim([
                'tracking_code' => $claim->shipment?->tracking_code,
                'type' => $claim->type,
                'amount' => number_format($claim->amount_cents / 100, 2, '.', ''),
                'description' => $claim->description,
            ]);
            $epId = $resp['id'] ?? null;
        } catch (\Throwable) {
            // keep local state — we can retry
        }

        $claim = $this->claims->transition(
            $claim,
            ['state' => 'submitted', 'ep_claim_id' => $epId],
            ['at' => now()->toIso8601String(), 'event' => 'submitted', 'by' => $user->id, 'ep_id' => $epId],
        );

        return $this->helper->toIdentity($claim);
    }
}

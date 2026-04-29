<?php

namespace App\Actions\Insurance;

use App\Models\Insurance;
use App\Models\User;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Care\InsuranceRepo;
use RuntimeException;

class CreateStandaloneInsuranceAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly InsuranceRepo $insurances,
    ) {}

    /**
     * Create a standalone insurance policy for a package already in transit with another carrier.
     * Returns the stored Insurance row; EP response is best-effort (the EP endpoint will reject
     * shipments past pre_transit — that error is surfaced in `messages`).
     */
    public function execute(User $user, array $input): Insurance
    {
        $teamId = (int) $user->current_team_id;

        $amountCents = (int) $input['amount_cents'];
        if ($amountCents <= 0) {
            throw new RuntimeException('Amount must be positive.');
        }

        $ep = null;
        $messages = [];
        try {
            $ep = $this->ep->createInsurance([
                'to_address' => $input['to_address'] ?? null,
                'from_address' => $input['from_address'] ?? null,
                'tracking_code' => $input['tracking_code'],
                'carrier' => $input['carrier'],
                'amount' => number_format($amountCents / 100, 2, '.', ''),
                'reference' => $input['reference'] ?? null,
            ])->json();
        } catch (\Throwable $e) {
            $messages['error'] = $e->getMessage();
        }

        /** @var Insurance $insurance */
        $insurance = $this->insurances->create([
            'team_id' => $teamId,
            'shipment_id' => $input['shipment_id'] ?? null,
            'ep_insurance_id' => $ep['id'] ?? null,
            'provider' => $ep['provider'] ?? 'EasyPost',
            'tracking_code' => $input['tracking_code'],
            'carrier' => $input['carrier'],
            'amount_cents' => $amountCents,
            'fee_cents' => isset($ep['fee']['amount']) ? (int) round(((float) $ep['fee']['amount']) * 100) : null,
            'reference' => $input['reference'] ?? null,
            'status' => $ep ? ($ep['status'] ?? 'new') : 'failed',
            'messages' => $messages ?: null,
        ])['Model'];

        return $insurance;
    }
}

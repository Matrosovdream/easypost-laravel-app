<?php

namespace App\Actions\Claims;

use App\Models\Claim;
use App\Models\User;
use App\Repositories\Care\ClaimRepo;
use App\Repositories\Shipping\ShipmentRepo;
use RuntimeException;

class OpenClaimAction
{
    public function __construct(
        private readonly ClaimRepo $claims,
        private readonly ShipmentRepo $shipments,
    ) {}

    public function execute(User $user, array $input): Claim
    {
        $teamId = (int) $user->current_team_id;

        $shipment = $this->shipments->findUnscoped((int) $input['shipment_id']);
        if (! $shipment || $shipment->team_id !== $teamId) {
            throw new RuntimeException('Shipment not found.');
        }

        $type = $input['type'] ?? 'damage';
        if (! in_array($type, ['damage', 'loss', 'missing_items'], true)) {
            throw new RuntimeException("Unsupported claim type: {$type}.");
        }

        /** @var Claim $claim */
        $claim = $this->claims->create([
            'team_id' => $teamId,
            'shipment_id' => $shipment->id,
            'insurance_id' => $input['insurance_id'] ?? null,
            'type' => $type,
            'amount_cents' => (int) $input['amount_cents'],
            'description' => $input['description'],
            'state' => 'open',
            'timeline' => [[
                'at' => now()->toIso8601String(),
                'event' => 'opened',
                'by' => $user->id,
            ]],
            'assigned_to' => $input['assigned_to'] ?? null,
        ])['Model'];

        return $claim;
    }
}

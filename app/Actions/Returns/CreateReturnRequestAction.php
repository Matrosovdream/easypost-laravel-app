<?php

namespace App\Actions\Returns;

use App\Models\ReturnRequest;
use App\Models\User;
use App\Repositories\Care\ReturnRequestRepo;
use App\Repositories\Shipping\ShipmentRepo;
use RuntimeException;

class CreateReturnRequestAction
{
    public function __construct(
        private readonly ReturnRequestRepo $returns,
        private readonly ShipmentRepo $shipments,
    ) {}

    public function execute(User $user, array $input): ReturnRequest
    {
        $teamId = (int) $user->current_team_id;

        $original = $this->shipments->findUnscoped((int) $input['original_shipment_id']);
        if (! $original || $original->team_id !== $teamId) {
            throw new RuntimeException('Original shipment not found.');
        }

        /** @var ReturnRequest $return */
        $return = $this->returns->create([
            'team_id' => $teamId,
            'client_id' => $original->client_id,
            'original_shipment_id' => $original->id,
            'reason' => $input['reason'] ?? null,
            'items' => $input['items'] ?? null,
            'notes' => $input['notes'] ?? null,
            'status' => 'requested',
            'auto_refund' => (bool) ($input['auto_refund'] ?? false),
            'created_by' => $user->id,
        ])['Model'];

        return $return;
    }
}

<?php

namespace App\Actions\Shipments;

use App\Models\Approval;
use App\Models\User;
use App\Repositories\Shipping\ApprovalRepo;
use App\Repositories\Shipping\ShipmentEventRepo;
use App\Repositories\Shipping\ShipmentRepo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ApproveShipmentAction
{
    public function __construct(
        private readonly BuyShipmentAction $buy,
        private readonly ShipmentRepo $shipments,
        private readonly ShipmentEventRepo $events,
        private readonly ApprovalRepo $approvals,
    ) {}

    public function execute(User $approver, Approval $approval, bool $buyImmediately = true): array
    {
        if ($approval->status !== 'pending') {
            throw new RuntimeException("Approval already resolved as '{$approval->status}'.");
        }

        return DB::transaction(function () use ($approver, $approval, $buyImmediately) {
            $approval = $this->approvals->markApproved($approval, $approver->id);

            $shipment = $this->shipments->findUnscoped($approval->shipment_id)
                ?? throw new RuntimeException('Shipment not found for approval.');

            $this->events->record($shipment->id, 'approval_accepted', ['approval_id' => $approval->id], $approver->id);

            if (! $buyImmediately) {
                $shipment = $this->shipments->updateStatus($shipment, ['status' => 'rated']);
                return ['approval' => $approval, 'shipment' => $shipment, 'buy' => null];
            }

            $buy = $this->buy->buyNow($approver, $shipment, $approval->rate_snapshot ?? []);

            return ['approval' => $approval, 'shipment' => $buy['shipment'] ?? $shipment->fresh(), 'buy' => $buy];
        });
    }
}

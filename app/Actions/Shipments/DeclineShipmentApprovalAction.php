<?php

namespace App\Actions\Shipments;

use App\Models\Approval;
use App\Models\User;
use App\Repositories\Shipping\ApprovalRepo;
use App\Repositories\Shipping\ShipmentEventRepo;
use App\Repositories\Shipping\ShipmentRepo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DeclineShipmentApprovalAction
{
    public function __construct(
        private readonly ApprovalRepo $approvals,
        private readonly ShipmentRepo $shipments,
        private readonly ShipmentEventRepo $events,
    ) {}

    public function execute(User $approver, Approval $approval, ?string $reason = null): Approval
    {
        if ($approval->status !== 'pending') {
            throw new RuntimeException("Approval already resolved as '{$approval->status}'.");
        }

        return DB::transaction(function () use ($approver, $approval, $reason) {
            $approval = $this->approvals->markDeclined($approval, $approver->id, $reason);

            $shipment = $this->shipments->findUnscoped($approval->shipment_id)
                ?? throw new RuntimeException('Shipment not found for approval.');
            $this->shipments->updateStatus($shipment, ['status' => 'rate_declined']);

            $this->events->record(
                $shipment->id,
                'approval_declined',
                ['approval_id' => $approval->id, 'reason' => $reason],
                $approver->id,
            );

            return $approval;
        });
    }
}

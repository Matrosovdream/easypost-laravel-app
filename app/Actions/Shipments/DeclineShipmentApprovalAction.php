<?php

namespace App\Actions\Shipments;

use App\Helpers\Shipments\ApprovalHelper;
use App\Models\Shipment;
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
        private readonly ApprovalHelper $helper,
    ) {}

    public function execute(User $approver, int $approvalId, ?string $reason = null): array
    {
        abort_unless($approver->can('approve', Shipment::class), 403);

        $approval = $this->approvals->findInTeam((int) $approver->current_team_id, $approvalId);
        abort_if(! $approval, 404);

        if ($approval->status !== 'pending') {
            throw new RuntimeException("Approval already resolved as '{$approval->status}'.");
        }

        DB::transaction(function () use ($approver, $approval, $reason) {
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
        });

        return $this->helper->toDeclinedResult();
    }
}

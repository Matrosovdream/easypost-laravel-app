<?php

namespace App\Actions\Shipments;

use App\Helpers\Shipments\ApprovalHelper;
use App\Models\Shipment;
use App\Models\User;
use App\Repositories\Shipping\ApprovalRepo;

class ListApprovalsAction
{
    public function __construct(
        private readonly ApprovalRepo $approvals,
        private readonly ApprovalHelper $helper,
    ) {}

    public function execute(User $user, ?string $status = 'pending', int $perPage = 25): array
    {
        abort_unless($user->can('approve', Shipment::class), 403);

        $page = $this->approvals->paginateForTeam(
            teamId: (int) $user->current_team_id,
            status: $status,
            perPage: $perPage,
        );

        return $this->helper->toListPayload($page);
    }
}

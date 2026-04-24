<?php

namespace App\Repositories\Shipping;

use App\Models\Approval;
use App\Repositories\AbstractRepo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApprovalRepo extends AbstractRepo
{
    protected $withRelations = ['shipment.toAddress', 'requester', 'approver'];

    public function __construct()
    {
        $this->model = new Approval();
    }

    public function findInTeam(int $teamId, int $id): ?Approval
    {
        return Approval::where('team_id', $teamId)->find($id);
    }

    public function paginateForTeam(int $teamId, ?string $status = 'pending', int $perPage = 25): LengthAwarePaginator
    {
        $q = Approval::where('team_id', $teamId)->with($this->withRelations);
        if ($status) $q->where('status', $status);
        return $q->orderByDesc('id')->paginate($perPage);
    }

    public function markApproved(Approval $approval, int $approverId): Approval
    {
        $approval->forceFill([
            'status' => 'approved',
            'approver_id' => $approverId,
            'resolved_at' => now(),
        ])->save();
        return $approval->fresh();
    }

    public function markDeclined(Approval $approval, int $approverId, ?string $reason): Approval
    {
        $approval->forceFill([
            'status' => 'declined',
            'approver_id' => $approverId,
            'resolved_at' => now(),
            'decline_reason' => $reason,
        ])->save();
        return $approval->fresh();
    }
}

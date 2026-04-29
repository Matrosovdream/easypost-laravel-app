<?php

namespace App\Repositories\Care;

use App\Models\Claim;
use App\Repositories\AbstractRepo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClaimRepo extends AbstractRepo
{
    protected $withRelations = ['shipment:id,reference,tracking_code', 'assignee:id,name', 'approver:id,name'];

    public function __construct()
    {
        $this->model = new Claim();
    }

    public function paginateForTeam(int $teamId, ?string $state = null, int $perPage = 25): LengthAwarePaginator
    {
        $q = Claim::where('team_id', $teamId)->with($this->withRelations);
        if ($state) $q->where('state', $state);
        return $q->orderByDesc('id')->paginate($perPage);
    }

    public function findInTeam(int $teamId, int $id): ?Claim
    {
        return Claim::with($this->withRelations)->where('team_id', $teamId)->find($id);
    }

    public function transition(Claim $claim, array $data, array $timelineEvent): Claim
    {
        $timeline = $claim->timeline ?? [];
        $timeline[] = $timelineEvent;
        $claim->forceFill(array_merge($data, ['timeline' => $timeline]))->save();
        return $claim->fresh();
    }
}

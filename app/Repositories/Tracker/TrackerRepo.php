<?php

namespace App\Repositories\Tracker;

use App\Models\Tracker;
use App\Repositories\AbstractRepo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TrackerRepo extends AbstractRepo
{
    protected $withRelations = ['team', 'events'];

    public function __construct()
    {
        $this->model = new Tracker();
    }

    public function getByTrackingCode(string $code): ?Tracker
    {
        return Tracker::where('tracking_code', $code)
            ->with($this->withRelations)
            ->first();
    }

    public function findByEpIdOrCode(?string $epId, ?string $code): ?Tracker
    {
        $q = Tracker::query();
        if ($epId) $q->orWhere('ep_tracker_id', $epId);
        if ($code) $q->orWhere('tracking_code', $code);
        return $q->first();
    }

    public function paginateForTeam(int $teamId, ?string $status = null, ?string $carrier = null, int $perPage = 25): LengthAwarePaginator
    {
        $q = Tracker::where('team_id', $teamId);
        if ($status) $q->where('status', $status);
        if ($carrier) $q->where('carrier', $carrier);
        return $q->orderByDesc('id')->paginate($perPage);
    }

    public function findWithEvents(int $id): ?Tracker
    {
        return Tracker::with('events')->find($id);
    }

    public function updateStatus(Tracker $tracker, array $data): Tracker
    {
        $tracker->forceFill($data)->save();
        return $tracker->fresh();
    }
}

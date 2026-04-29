<?php

namespace App\Repositories\Operations;

use App\Models\Pickup;
use App\Repositories\AbstractRepo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PickupRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new Pickup();
    }

    public function paginateForTeam(int $teamId, ?string $status = null, int $perPage = 25): LengthAwarePaginator
    {
        $q = Pickup::where('team_id', $teamId)->with('address:id,name,city,state');
        if ($status) $q->where('status', $status);
        return $q->orderByDesc('min_datetime')->paginate($perPage);
    }

    public function findWithAddress(int $id): ?Pickup
    {
        return Pickup::with('address')->find($id);
    }

    public function markScheduled(Pickup $pickup, array $data): Pickup
    {
        $pickup->forceFill(array_merge(['status' => 'scheduled'], $data))->save();
        return $pickup->fresh();
    }

    public function markCancelled(Pickup $pickup): Pickup
    {
        $pickup->forceFill(['status' => 'cancelled'])->save();
        return $pickup->fresh();
    }
}

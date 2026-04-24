<?php

namespace App\Repositories\Shipping;

use App\Models\Parcel;
use App\Repositories\AbstractRepo;

class ParcelRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new Parcel();
    }

    public function createForTeam(int $teamId, array $data): Parcel
    {
        return Parcel::create(array_merge($data, ['team_id' => $teamId]));
    }
}

<?php

namespace App\Repositories\Care;

use App\Models\Insurance;
use App\Repositories\AbstractRepo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InsuranceRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new Insurance();
    }

    public function paginateForTeam(int $teamId, int $perPage = 25): LengthAwarePaginator
    {
        return Insurance::where('team_id', $teamId)->orderByDesc('id')->paginate($perPage);
    }
}

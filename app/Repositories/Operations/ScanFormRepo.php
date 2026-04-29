<?php

namespace App\Repositories\Operations;

use App\Models\ScanForm;
use App\Repositories\AbstractRepo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ScanFormRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new ScanForm();
    }

    public function paginateForTeam(int $teamId, int $perPage = 25): LengthAwarePaginator
    {
        return ScanForm::where('team_id', $teamId)->orderByDesc('id')->paginate($perPage);
    }
}

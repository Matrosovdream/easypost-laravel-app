<?php

namespace App\Actions\Insurance;

use App\Helpers\Insurance\InsuranceHelper;
use App\Models\Insurance;
use App\Models\User;
use App\Repositories\Care\InsuranceRepo;
use Illuminate\Support\Facades\Gate;

class ListInsurancesAction
{
    public function __construct(
        private readonly InsuranceRepo $insurances,
        private readonly InsuranceHelper $helper,
    ) {}

    public function execute(User $user, int $perPage = 25): array
    {
        Gate::authorize('viewAny', Insurance::class);

        $page = $this->insurances->paginateForTeam(
            teamId: (int) $user->current_team_id,
            perPage: $perPage,
        );

        return $this->helper->toListPayload($page);
    }
}

<?php

use App\Actions\Insurance\ListInsurancesAction;
use App\Helpers\Insurance\InsuranceHelper;
use App\Models\Insurance;
use App\Models\User;
use App\Repositories\Care\InsuranceRepo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

it('ListInsurancesAction returns paginated payload after authorize', function () {
    Gate::shouldReceive('authorize')->with('viewAny', Insurance::class)->once();

    $repo = mock(InsuranceRepo::class);
    $repo->shouldReceive('paginateForTeam')->andReturn(new LengthAwarePaginator([], 0, 25, 1));

    $action = new ListInsurancesAction($repo, new InsuranceHelper());
    $user = new User();
    $user->current_team_id = 3;

    expect($action->execute($user))->toHaveKeys(['data', 'meta']);
});

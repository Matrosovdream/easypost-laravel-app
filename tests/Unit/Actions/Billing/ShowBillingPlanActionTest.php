<?php

use App\Actions\Billing\ShowBillingPlanAction;
use App\Helpers\Billing\BillingPlanHelper;
use App\Models\Team;
use App\Models\User;
use App\Repositories\Team\TeamRepo;
use App\Services\Billing\PlanCaps;

beforeEach(function () {
    $this->teams = mock(TeamRepo::class);
    $this->caps = mock(PlanCaps::class);
    $this->action = new ShowBillingPlanAction($this->teams, $this->caps, new BillingPlanHelper());
});

it('aborts 404 when team not found', function () {
    $user = new User();
    $user->current_team_id = 1;
    $this->teams->shouldReceive('getByID')->andReturn(null);

    expect(fn () => $this->action->execute($user))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('returns plan payload with usage shape', function () {
    $user = new User();
    $user->current_team_id = 1;

    $team = new Team(['plan' => 'team', 'status' => 'active', 'mode' => 'live']);
    $team->id = 1;

    $this->teams->shouldReceive('getByID')->andReturn(['Model' => $team]);
    $this->caps->shouldReceive('capForPlan')->with('team')->andReturn(1000);
    $this->caps->shouldReceive('usageForTeamThisMonth')->with(1)->andReturn(120);

    $out = $this->action->execute($user);

    expect($out)->toMatchArray(['plan' => 'team', 'status' => 'active']);
    expect($out['usage'])->toMatchArray(['used' => 120, 'cap' => 1000, 'remaining' => 880]);
});

<?php

use App\Actions\Billing\OpenBillingPortalAction;
use App\Helpers\Billing\BillingPlanHelper;
use App\Models\Team;
use App\Models\User;
use App\Repositories\Team\TeamRepo;

beforeEach(function () {
    $this->teams = mock(TeamRepo::class);
    $this->action = new OpenBillingPortalAction($this->teams, new BillingPlanHelper());
});

it('aborts 403 without billing.manage right', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn([]);

    expect(fn () => $this->action->execute($user))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('aborts 404 when team is not found', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn(['billing.manage']);
    $user->current_team_id = 1;
    $this->teams->shouldReceive('getByID')->andReturn(null);

    expect(fn () => $this->action->execute($user))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('returns simulated 200 portal URL in test env when Stripe call throws', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn(['billing.manage']);
    $user->current_team_id = 1;

    $team = mock(Team::class)->makePartial();
    $team->shouldReceive('billingPortalUrl')->andThrow(new \RuntimeException('Stripe down'));

    $this->teams->shouldReceive('getByID')->andReturn(['Model' => $team]);

    $out = $this->action->execute($user);
    expect($out['_status'])->toBe(200);
    expect($out['body']['simulated'])->toBeTrue();
});

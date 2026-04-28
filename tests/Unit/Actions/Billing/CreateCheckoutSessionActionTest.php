<?php

use App\Actions\Billing\CreateCheckoutSessionAction;
use App\Helpers\Billing\BillingPlanHelper;
use App\Models\User;
use App\Repositories\Team\TeamRepo;

beforeEach(function () {
    $this->teams = mock(TeamRepo::class);
    $this->action = new CreateCheckoutSessionAction($this->teams, new BillingPlanHelper());
});

it('aborts 403 when user lacks billing.manage right', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn([]);

    expect(fn () => $this->action->execute($user, 'team'))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('returns 200 simulated envelope when price id is placeholder in test env', function () {
    config()->set('billing.prices.team', 'price_placeholder_team');
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn(['billing.manage']);

    $out = $this->action->execute($user, 'team');

    expect($out['_status'])->toBe(200);
    expect($out['body']['simulated'])->toBeTrue();
});

it('returns 422 unknown plan envelope when price id missing in non-test env', function () {
    config()->set('billing.prices.team', null);
    config()->set('app.env', 'production');
    app()['env'] = 'production'; // override env in container

    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn(['billing.manage']);

    $out = $this->action->execute($user, 'team');

    expect($out['_status'])->toBe(422);
    expect($out['body']['message'])->toBe('Unknown plan.');
});

<?php

use App\Helpers\Billing\BillingPlanHelper;
use App\Models\Team;

beforeEach(function () {
    $this->helper = new BillingPlanHelper();
});

it('isPlaceholderPriceId is true for null/empty/placeholder', function () {
    expect($this->helper->isPlaceholderPriceId(null))->toBeTrue();
    expect($this->helper->isPlaceholderPriceId(''))->toBeTrue();
    expect($this->helper->isPlaceholderPriceId('price_placeholder_team'))->toBeTrue();
    expect($this->helper->isPlaceholderPriceId('price_real_123'))->toBeFalse();
});

it('toCheckoutSimulated builds an in-app simulated URL', function () {
    $out = $this->helper->toCheckoutSimulated('team');
    expect($out['simulated'])->toBeTrue();
    expect($out['url'])->toContain('checkout=simulated');
    expect($out['url'])->toContain('plan=team');
});

it('toPortalSimulated builds an in-app simulated portal URL', function () {
    $out = $this->helper->toPortalSimulated();
    expect($out['simulated'])->toBeTrue();
    expect($out['url'])->toContain('portal=simulated');
});

it('toPlanPayload includes usage with remaining when cap is set', function () {
    $team = new Team(['plan' => 'team', 'status' => 'active', 'mode' => 'live']);
    $team->id = 1;

    $out = $this->helper->toPlanPayload($team, used: 30, cap: 100);

    expect($out['plan'])->toBe('team');
    expect($out['usage'])->toMatchArray([
        'used' => 30,
        'cap' => 100,
        'remaining' => 70,
    ]);
});

it('toPlanPayload usage.remaining is null for unlimited cap', function () {
    $team = new Team(['plan' => 'enterprise']);
    $team->id = 1;
    $out = $this->helper->toPlanPayload($team, used: 9999, cap: null);
    expect($out['usage']['remaining'])->toBeNull();
});

it('toPlanPayload remaining clamps to 0 when over cap', function () {
    $team = new Team(['plan' => 'starter']);
    $team->id = 1;
    $out = $this->helper->toPlanPayload($team, used: 200, cap: 100);
    expect($out['usage']['remaining'])->toBe(0);
});

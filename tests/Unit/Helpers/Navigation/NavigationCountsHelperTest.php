<?php

use App\Helpers\Analytics\AnalyticsOverviewHelper;
use App\Helpers\Navigation\NavigationCountsHelper;
use App\Models\User;

beforeEach(function () {
    $this->analytics = mock(AnalyticsOverviewHelper::class);
    $this->helper = new NavigationCountsHelper($this->analytics);
});

it('defaultCounts returns zeroed canonical shape', function () {
    expect($this->helper->defaultCounts())->toBe([
        'approvalsCount' => 0,
        'exceptionsCount' => 0,
        'returnsCount' => 0,
        'claimsCount' => 0,
        'queueCount' => 0,
        'printReady' => 0,
    ]);
});

it('buildForUser short-circuits when user has no current_team_id', function () {
    $user = mock(User::class)->makePartial();
    $user->current_team_id = null;
    $user->shouldReceive('rights')->andReturn([]);

    $this->analytics->shouldNotReceive('pendingApprovalsCount');
    expect($this->helper->buildForUser($user))->toBe($this->helper->defaultCounts());
});

it('buildForUser populates approvalsCount when user has shipments.approve right', function () {
    $user = mock(User::class)->makePartial();
    $user->current_team_id = 7;
    $user->shouldReceive('rights')->andReturn(['shipments.approve']);

    $this->analytics->shouldReceive('pendingApprovalsCount')->with(7)->andReturn(4);

    expect($this->helper->buildForUser($user)['approvalsCount'])->toBe(4);
});

it('buildForUser populates exceptionsCount when user has trackers.view right', function () {
    $user = mock(User::class)->makePartial();
    $user->current_team_id = 7;
    $user->shouldReceive('rights')->andReturn(['trackers.view']);

    $this->analytics->shouldReceive('trackerExceptionsCount')->with(7)->andReturn(2);

    expect($this->helper->buildForUser($user)['exceptionsCount'])->toBe(2);
});

it('buildForUser populates printReady when user has labels.print right', function () {
    $user = mock(User::class)->makePartial();
    $user->current_team_id = 7;
    $user->shouldReceive('rights')->andReturn(['labels.print']);

    $this->analytics->shouldReceive('printReadyCount')->with(7)->andReturn(9);

    expect($this->helper->buildForUser($user)['printReady'])->toBe(9);
});

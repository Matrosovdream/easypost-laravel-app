<?php

use App\Helpers\Team\TeamHelper;
use App\Models\Team;

beforeEach(function () {
    $this->helper = new TeamHelper();
});

it('toDetail flattens core team fields', function () {
    $team = new Team([
        'name' => 'Acme',
        'plan' => 'team',
        'mode' => 'live',
        'status' => 'active',
        'time_zone' => 'UTC',
        'default_currency' => 'USD',
        'settings' => ['x' => 1],
        'logo_s3_key' => 's3://x.png',
    ]);
    $team->id = 1;

    $out = $this->helper->toDetail($team);

    expect($out)->toBe([
        'id' => 1,
        'name' => 'Acme',
        'plan' => 'team',
        'mode' => 'live',
        'status' => 'active',
        'time_zone' => 'UTC',
        'default_currency' => 'USD',
        'settings' => ['x' => 1],
        'logo_s3_key' => 's3://x.png',
    ]);
});

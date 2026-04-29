<?php

use App\Helpers\Users\UserManagementHelper;

beforeEach(function () {
    $this->helper = new UserManagementHelper();
});

it('toListItem maps stdClass row with bool cast on is_active', function () {
    $row = (object) [
        'id' => 1, 'name' => 'Stan', 'email' => 's@x.test',
        'role_slug' => 'admin', 'role_name' => 'Admin',
        'is_active' => 1,
        'last_login_at' => '2026-01-01',
        'spending_cap_cents' => 5000, 'daily_cap_cents' => 1000,
        'client_id' => null, 'membership_status' => 'active',
    ];

    expect($this->helper->toListItem($row))->toMatchArray([
        'id' => 1,
        'name' => 'Stan',
        'role_slug' => 'admin',
        'is_active' => true,
        'membership_status' => 'active',
    ]);
});

it('toListPayload wraps a Collection', function () {
    $rows = collect([
        (object) [
            'id' => 1, 'name' => 'A', 'email' => 'a@x', 'role_slug' => 'r', 'role_name' => 'R',
            'is_active' => true, 'last_login_at' => null,
            'spending_cap_cents' => null, 'daily_cap_cents' => null,
            'client_id' => null, 'membership_status' => 'active',
        ],
    ]);
    $out = $this->helper->toListPayload($rows);
    expect($out['data']->count())->toBe(1);
});

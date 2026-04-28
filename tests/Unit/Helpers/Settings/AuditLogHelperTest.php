<?php

use App\Helpers\Settings\AuditLogHelper;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->helper = new AuditLogHelper();
});

it('toListItem decodes meta JSON when present', function () {
    $row = (object) [
        'id' => 1, 'action' => 'auth.login',
        'user_name' => 'Stan', 'user_email' => 'stan@x.test',
        'subject_type' => 'App\\Models\\User', 'subject_id' => 7,
        'meta' => json_encode(['method' => 'pin']),
        'ip' => '10.0.0.1', 'created_at' => '2026-01-01',
    ];

    expect($this->helper->toListItem($row))->toMatchArray([
        'id' => 1,
        'action' => 'auth.login',
        'user' => ['name' => 'Stan', 'email' => 'stan@x.test'],
        'meta' => ['method' => 'pin'],
        'ip' => '10.0.0.1',
    ]);
});

it('toListItem returns null user when name missing', function () {
    $row = (object) [
        'id' => 1, 'action' => 'a', 'user_name' => null, 'user_email' => null,
        'subject_type' => null, 'subject_id' => null, 'meta' => null,
        'ip' => null, 'created_at' => null,
    ];
    expect($this->helper->toListItem($row)['user'])->toBeNull();
    expect($this->helper->toListItem($row)['meta'])->toBeNull();
});

it('toListPayload wraps a paginator', function () {
    $row = (object) [
        'id' => 1, 'action' => 'a', 'user_name' => null, 'user_email' => null,
        'subject_type' => null, 'subject_id' => null, 'meta' => null,
        'ip' => null, 'created_at' => null,
    ];
    $page = new LengthAwarePaginator([$row], 1, 50, 1);
    expect($this->helper->toListPayload($page)['meta']['total'])->toBe(1);
});

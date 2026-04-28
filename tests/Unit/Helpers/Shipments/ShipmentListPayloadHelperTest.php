<?php

use App\Helpers\Shipments\ShipmentListPayloadHelper;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->helper = new ShipmentListPayloadHelper();
});

it('toListPayload omits per_page by default', function () {
    $page = new LengthAwarePaginator([], 0, 25, 1);
    $out = $this->helper->toListPayload($page);
    expect($out['meta'])->toHaveKeys(['current_page', 'last_page', 'total']);
    expect($out['meta'])->not->toHaveKey('per_page');
});

it('toListPayload includes per_page when requested', function () {
    $page = new LengthAwarePaginator([], 0, 25, 1);
    $out = $this->helper->toListPayload($page, includePerPage: true);
    expect($out['meta']['per_page'])->toBe(25);
});

it('data is an array (after Resource resolve)', function () {
    $page = new LengthAwarePaginator([], 0, 25, 1);
    expect($this->helper->toListPayload($page)['data'])->toBeArray();
});

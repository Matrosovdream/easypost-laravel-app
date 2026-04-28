<?php

use App\Helpers\Addresses\AddressHelper;
use App\Models\Address;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->helper = new AddressHelper();
});

it('shapes a single address into the list-item array', function () {
    $a = new Address([
        'name' => 'Stan',
        'company' => 'Acme',
        'street1' => '1 Main',
        'street2' => 'Apt 2',
        'city' => 'NYC',
        'state' => 'NY',
        'zip' => '10001',
        'country' => 'US',
        'phone' => '555-0100',
        'email' => 'stan@example.com',
        'residential' => true,
        'verified' => true,
        'ep_address_id' => 'adr_x',
        'client_id' => 7,
    ]);
    $a->id = 42;
    $a->verified_at = now();
    $a->created_at = now();

    $out = $this->helper->toListItem($a);

    expect($out)->toMatchArray([
        'id' => 42,
        'name' => 'Stan',
        'company' => 'Acme',
        'street1' => '1 Main',
        'city' => 'NYC',
        'verified' => true,
        'ep_address_id' => 'adr_x',
        'client_id' => 7,
    ]);
    expect($out['verified_at'])->toBeString();
    expect($out['created_at'])->toBeString();
});

it('toDetail mirrors toListItem', function () {
    $a = new Address(['name' => 'X', 'street1' => 'a', 'country' => 'US']);
    $a->id = 1;
    expect($this->helper->toDetail($a))->toBe($this->helper->toListItem($a));
});

it('toListPayload wraps a paginator with data + meta', function () {
    $a = new Address(['name' => 'X', 'street1' => 'a', 'country' => 'US']);
    $a->id = 1;
    $page = new LengthAwarePaginator([$a], 1, 25, 1);

    $out = $this->helper->toListPayload($page);

    expect($out)->toHaveKeys(['data', 'meta']);
    expect($out['data']->count())->toBe(1);
    expect($out['meta'])->toBe(['current_page' => 1, 'last_page' => 1, 'total' => 1]);
});

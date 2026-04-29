<?php

use App\Helpers\Pickups\PickupHelper;
use App\Models\Address;
use App\Models\Pickup;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->helper = new PickupHelper();
});

function makePickup(): Pickup
{
    $p = new Pickup([
        'reference' => 'P-1',
        'status' => 'scheduled',
        'carrier' => 'USPS',
        'service' => 'NextDay',
        'confirmation' => 'CNF',
        'cost_cents' => 1000,
        'instructions' => 'leave at door',
        'rates_snapshot' => [['rate' => '5.00']],
    ]);
    $p->id = 1;
    $p->min_datetime = now();
    $p->max_datetime = now();
    return $p;
}

it('toListItem nests address summary when present', function () {
    $pickup = makePickup();
    $addr = new Address(['name' => 'Stan', 'city' => 'NYC', 'state' => 'NY']);
    $pickup->setRelation('address', $addr);

    $out = $this->helper->toListItem($pickup);
    expect($out['address'])->toBe(['name' => 'Stan', 'city' => 'NYC', 'state' => 'NY']);
});

it('toListItem returns null address when missing', function () {
    $pickup = makePickup();
    $pickup->setRelation('address', null);
    expect($this->helper->toListItem($pickup)['address'])->toBeNull();
});

it('toDetail includes instructions and rates', function () {
    $pickup = makePickup();
    $pickup->setRelation('address', null);
    $out = $this->helper->toDetail($pickup);
    expect($out['instructions'])->toBe('leave at door');
    expect($out['rates'])->toBe([['rate' => '5.00']]);
});

it('toScheduledPayload returns id+status+rates', function () {
    $pickup = makePickup();
    expect($this->helper->toScheduledPayload($pickup))->toBe([
        'id' => 1, 'status' => 'scheduled', 'rates' => [['rate' => '5.00']],
    ]);
});

it('toBuyResult returns id+status+confirmation', function () {
    $pickup = makePickup();
    expect($this->helper->toBuyResult($pickup))->toBe([
        'id' => 1, 'status' => 'scheduled', 'confirmation' => 'CNF',
    ]);
});

it('toCancelResult returns id+status', function () {
    $pickup = makePickup();
    expect($this->helper->toCancelResult($pickup))->toBe(['id' => 1, 'status' => 'scheduled']);
});

it('toListPayload wraps a paginator', function () {
    $pickup = makePickup();
    $pickup->setRelation('address', null);
    $page = new LengthAwarePaginator([$pickup], 1, 25, 1);
    expect($this->helper->toListPayload($page)['meta']['total'])->toBe(1);
});

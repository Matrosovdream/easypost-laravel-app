<?php

use App\Helpers\Ops\PrintQueueHelper;
use App\Models\Address;
use App\Models\Shipment;

beforeEach(function () {
    $this->helper = new PrintQueueHelper();
});

it('toListItem nests to_address summary when present', function () {
    $s = new Shipment([
        'reference' => 'R', 'tracking_code' => 'TC',
        'carrier' => 'USPS', 'service' => 'priority',
        'label_s3_key' => 's3://l.pdf', 'assigned_to' => 4,
    ]);
    $s->id = 10;
    $addr = new Address(['name' => 'Stan', 'city' => 'NYC', 'state' => 'NY', 'country' => 'US']);
    $s->setRelation('toAddress', $addr);

    $out = $this->helper->toListItem($s);
    expect($out['to_address'])->toBe([
        'name' => 'Stan', 'city' => 'NYC', 'state' => 'NY', 'country' => 'US',
    ]);
});

it('toListItem returns null to_address when missing', function () {
    $s = new Shipment(['reference' => null, 'tracking_code' => null]);
    $s->id = 1;
    $s->setRelation('toAddress', null);
    expect($this->helper->toListItem($s)['to_address'])->toBeNull();
});

it('toListPayload wraps a Collection', function () {
    $s = new Shipment(['reference' => null, 'tracking_code' => null]);
    $s->id = 1;
    $s->setRelation('toAddress', null);
    $out = $this->helper->toListPayload(collect([$s]));
    expect($out['data']->count())->toBe(1);
});

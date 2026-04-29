<?php

use App\Helpers\Returns\ReturnHelper;
use App\Models\ReturnRequest;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->helper = new ReturnHelper();
});

function makeReturn(): ReturnRequest
{
    $r = new ReturnRequest([
        'status' => 'requested',
        'reason' => 'damaged',
        'original_shipment_id' => 11,
        'return_shipment_id' => null,
        'auto_refund' => false,
        'items' => [['sku' => 'A', 'qty' => 1]],
        'notes' => 'pls',
    ]);
    $r->id = 5;
    $r->created_at = now();
    return $r;
}

it('toListItem includes creator when present', function () {
    $r = makeReturn();
    $u = new User(['name' => 'Stan']);
    $u->id = 1;
    $r->setRelation('creator', $u);

    $out = $this->helper->toListItem($r);
    expect($out['created_by'])->toBe(['id' => 1, 'name' => 'Stan']);
    expect($out['status'])->toBe('requested');
});

it('toIdentity returns id+status', function () {
    $r = makeReturn();
    expect($this->helper->toIdentity($r))->toBe(['id' => 5, 'status' => 'requested']);
});

it('toApprovedResult returns id+status+return_shipment_id', function () {
    $r = makeReturn();
    $r->return_shipment_id = 99;
    $r->status = 'approved';
    expect($this->helper->toApprovedResult($r))->toBe([
        'id' => 5, 'status' => 'approved', 'return_shipment_id' => 99,
    ]);
});

it('toDetail merges items, notes, and shipments', function () {
    $r = makeReturn();
    $r->setRelation('creator', null);
    $r->setRelation('approver', null);
    $r->approved_at = null;
    $orig = new Shipment(['reference' => 'O-1', 'tracking_code' => 'TC']);
    $orig->id = 11;
    $r->setRelation('originalShipment', $orig);
    $r->setRelation('returnShipment', null);

    $out = $this->helper->toDetail($r);
    expect($out['items'])->toBe([['sku' => 'A', 'qty' => 1]]);
    expect($out['notes'])->toBe('pls');
    expect($out['original_shipment'])->toBe(['id' => 11, 'reference' => 'O-1', 'tracking_code' => 'TC']);
    expect($out['return_shipment'])->toBeNull();
});

it('toListPayload wraps a paginator', function () {
    $r = makeReturn();
    $r->setRelation('creator', null);
    $page = new LengthAwarePaginator([$r], 1, 25, 1);
    expect($this->helper->toListPayload($page)['meta']['total'])->toBe(1);
});

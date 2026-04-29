<?php

use App\Helpers\Shipments\ApprovalHelper;
use App\Models\Address;
use App\Models\Approval;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->helper = new ApprovalHelper();
});

function makeApproval(): Approval
{
    $a = new Approval([
        'status' => 'pending',
        'cost_cents' => 1500,
        'reason' => 'over-cap',
        'note' => null,
        'rate_snapshot' => ['rate_id' => 'r_1'],
        'shipment_id' => 11,
    ]);
    $a->id = 5;
    $a->created_at = now();
    $a->resolved_at = null;
    return $a;
}

it('toListItem nests requester/approver/shipment relations', function () {
    $a = makeApproval();
    $req = new User(['name' => 'Stan']);
    $req->id = 1;
    $shipment = new Shipment(['reference' => 'R', 'status' => 'rated']);
    $shipment->id = 11;
    $addr = new Address(['city' => 'NYC', 'state' => 'NY', 'country' => 'US']);
    $shipment->setRelation('toAddress', $addr);

    $a->setRelation('requester', $req);
    $a->setRelation('approver', null);
    $a->setRelation('shipment', $shipment);

    $out = $this->helper->toListItem($a);
    expect($out['requested_by'])->toBe(['id' => 1, 'name' => 'Stan']);
    expect($out['approver'])->toBeNull();
    expect($out['shipment']['id'])->toBe(11);
    expect($out['shipment']['to_address'])->toBe(['city' => 'NYC', 'state' => 'NY', 'country' => 'US']);
});

it('toApprovedResult derives shipment_id from action result', function () {
    $shipment = new Shipment();
    $shipment->id = 99;
    $result = ['buy' => ['status' => 'queued'], 'shipment' => $shipment];

    expect($this->helper->toApprovedResult($result))->toBe([
        'status' => 'approved',
        'buy_status' => 'queued',
        'shipment_id' => 99,
    ]);
});

it('toApprovedResult handles null buy', function () {
    $shipment = new Shipment();
    $shipment->id = 5;
    $result = ['buy' => null, 'shipment' => $shipment];
    expect($this->helper->toApprovedResult($result)['buy_status'])->toBeNull();
});

it('toDeclinedResult returns canonical payload', function () {
    expect($this->helper->toDeclinedResult())->toBe(['status' => 'declined']);
});

it('toListPayload wraps paginator', function () {
    $a = makeApproval();
    $a->setRelation('requester', null);
    $a->setRelation('approver', null);
    $a->setRelation('shipment', null);
    $page = new LengthAwarePaginator([$a], 1, 25, 1);
    expect($this->helper->toListPayload($page)['meta']['total'])->toBe(1);
});

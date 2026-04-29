<?php

use App\Helpers\Claims\ClaimHelper;
use App\Models\Claim;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->helper = new ClaimHelper();
});

function makeClaim(array $attrs = []): Claim
{
    $c = new Claim(array_merge([
        'state' => 'open',
        'type' => 'damage',
        'amount_cents' => 1000,
        'recovered_cents' => 0,
        'shipment_id' => 11,
    ], $attrs));
    $c->id = $attrs['id'] ?? 5;
    $c->created_at = now();
    return $c;
}

it('toListItem nests shipment + assignee + approver when present', function () {
    $claim = makeClaim();
    $shipment = new Shipment(['reference' => 'S-1', 'tracking_code' => 'TC1']);
    $shipment->id = 11;
    $assignee = new User(['name' => 'Stan']);
    $assignee->id = 1;
    $approver = new User(['name' => 'Pat']);
    $approver->id = 2;

    $claim->setRelation('shipment', $shipment);
    $claim->setRelation('assignee', $assignee);
    $claim->setRelation('approver', $approver);

    $out = $this->helper->toListItem($claim);

    expect($out['shipment'])->toBe(['id' => 11, 'reference' => 'S-1', 'tracking_code' => 'TC1']);
    expect($out['assignee'])->toBe(['id' => 1, 'name' => 'Stan']);
    expect($out['approver'])->toBe(['id' => 2, 'name' => 'Pat']);
});

it('toListItem returns null nests when relations missing', function () {
    $claim = makeClaim();
    $claim->setRelation('shipment', null);
    $claim->setRelation('assignee', null);
    $claim->setRelation('approver', null);
    $out = $this->helper->toListItem($claim);
    expect($out['shipment'])->toBeNull();
    expect($out['assignee'])->toBeNull();
    expect($out['approver'])->toBeNull();
});

it('toDetail merges in description/timeline/ep_claim_id', function () {
    $claim = makeClaim();
    $claim->setRelation('shipment', null);
    $claim->setRelation('assignee', null);
    $claim->setRelation('approver', null);
    $claim->description = 'busted';
    $claim->timeline = [['event' => 'opened']];
    $claim->ep_claim_id = 'clm_x';

    $out = $this->helper->toDetail($claim);
    expect($out['description'])->toBe('busted');
    expect($out['timeline'])->toBe([['event' => 'opened']]);
    expect($out['ep_claim_id'])->toBe('clm_x');
});

it('toIdentity returns id+state', function () {
    $claim = makeClaim();
    expect($this->helper->toIdentity($claim))->toBe(['id' => 5, 'state' => 'open']);
});

it('toApprovedResult returns id+state+recovered_cents', function () {
    $claim = makeClaim(['recovered_cents' => 950]);
    expect($this->helper->toApprovedResult($claim))->toBe([
        'id' => 5, 'state' => 'open', 'recovered_cents' => 950,
    ]);
});

it('toListPayload wraps a paginator', function () {
    $claim = makeClaim();
    $claim->setRelation('shipment', null);
    $claim->setRelation('assignee', null);
    $claim->setRelation('approver', null);
    $page = new LengthAwarePaginator([$claim], 1, 25, 1);

    $out = $this->helper->toListPayload($page);
    expect($out['data']->count())->toBe(1);
    expect($out['meta']['total'])->toBe(1);
});

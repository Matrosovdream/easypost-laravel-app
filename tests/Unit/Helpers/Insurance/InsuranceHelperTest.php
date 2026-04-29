<?php

use App\Helpers\Insurance\InsuranceHelper;
use App\Models\Insurance;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->helper = new InsuranceHelper();
});

function makeInsurance(): Insurance
{
    $i = new Insurance([
        'tracking_code' => 'TC',
        'carrier' => 'USPS',
        'amount_cents' => 5000,
        'fee_cents' => 100,
        'provider' => 'EasyPost',
        'status' => 'new',
        'reference' => 'REF',
        'shipment_id' => 7,
        'ep_insurance_id' => 'ins_x',
        'messages' => null,
    ]);
    $i->id = 9;
    $i->created_at = now();
    return $i;
}

it('toListItem includes core fields', function () {
    $i = makeInsurance();
    expect($this->helper->toListItem($i))->toMatchArray([
        'id' => 9,
        'tracking_code' => 'TC',
        'carrier' => 'USPS',
        'amount_cents' => 5000,
        'fee_cents' => 100,
        'status' => 'new',
        'shipment_id' => 7,
    ]);
});

it('toCreatedPayload returns id+status+ep_insurance_id+messages', function () {
    $i = makeInsurance();
    expect($this->helper->toCreatedPayload($i))->toBe([
        'id' => 9, 'status' => 'new', 'ep_insurance_id' => 'ins_x', 'messages' => null,
    ]);
});

it('toListPayload wraps a paginator', function () {
    $page = new LengthAwarePaginator([makeInsurance()], 1, 25, 1);
    $out = $this->helper->toListPayload($page);
    expect($out['data']->count())->toBe(1);
    expect($out['meta'])->toBe(['current_page' => 1, 'last_page' => 1, 'total' => 1]);
});

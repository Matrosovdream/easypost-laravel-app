<?php

use App\Helpers\ScanForms\ScanFormHelper;
use App\Models\ScanForm;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->helper = new ScanFormHelper();
});

function makeForm(): ScanForm
{
    $f = new ScanForm([
        'carrier' => 'USPS',
        'status' => 'created',
        'form_pdf_s3_key' => 's3://x.pdf',
        'tracking_codes' => ['TC1', 'TC2'],
        'from_address_id' => 7,
    ]);
    $f->id = 3;
    $f->created_at = now();
    return $f;
}

it('toListItem includes carrier/status/tracking_codes', function () {
    $f = makeForm();
    expect($this->helper->toListItem($f))->toMatchArray([
        'id' => 3, 'carrier' => 'USPS', 'status' => 'created',
        'form_url' => 's3://x.pdf', 'tracking_codes' => ['TC1', 'TC2'],
    ]);
});

it('toDetail adds from_address_id', function () {
    $f = makeForm();
    expect($this->helper->toDetail($f)['from_address_id'])->toBe(7);
});

it('toCreatedPayload returns id+status+form_url', function () {
    $f = makeForm();
    expect($this->helper->toCreatedPayload($f))->toBe([
        'id' => 3, 'status' => 'created', 'form_url' => 's3://x.pdf',
    ]);
});

it('toListPayload wraps a paginator', function () {
    $page = new LengthAwarePaginator([makeForm()], 1, 25, 1);
    expect($this->helper->toListPayload($page)['meta']['total'])->toBe(1);
});

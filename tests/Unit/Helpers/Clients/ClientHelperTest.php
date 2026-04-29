<?php

use App\Helpers\Clients\ClientHelper;
use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;

beforeEach(function () {
    $this->helper = new ClientHelper();
});

it('toListItem casts flexrate_markup_pct to float and includes core fields', function () {
    $c = new Client([
        'company_name' => 'Acme',
        'contact_name' => 'Stan',
        'contact_email' => 'stan@acme.test',
        'contact_phone' => '555',
        'flexrate_markup_pct' => '12.5',
        'per_service_markups' => ['ground' => 10],
        'billing_mode' => 'invoice',
        'credit_terms_days' => 30,
        'status' => 'active',
        'ep_endshipper_id' => 'es_x',
        'notes' => 'n',
    ]);
    $c->id = 1;
    $c->created_at = now();

    $out = $this->helper->toListItem($c);

    expect($out['flexrate_markup_pct'])->toBe(12.5);
    expect($out['per_service_markups'])->toBe(['ground' => 10]);
    expect($out['company_name'])->toBe('Acme');
    expect($out['status'])->toBe('active');
});

it('toDetail mirrors toListItem', function () {
    $c = new Client(['company_name' => 'X', 'flexrate_markup_pct' => 0]);
    $c->id = 1;
    expect($this->helper->toDetail($c))->toBe($this->helper->toListItem($c));
});

it('toListPayload wraps a Collection of clients', function () {
    $c1 = new Client(['company_name' => 'A', 'flexrate_markup_pct' => 0]);
    $c1->id = 1;
    $c2 = new Client(['company_name' => 'B', 'flexrate_markup_pct' => 0]);
    $c2->id = 2;

    $rows = new Collection([$c1, $c2]);
    $out = $this->helper->toListPayload($rows);

    expect($out['data']->count())->toBe(2);
    expect($out['data'][0]['id'])->toBe(1);
});

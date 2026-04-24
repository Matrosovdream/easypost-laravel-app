<?php

use App\Models\Insurance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Cache::flush();
});

it('admin can create standalone insurance', function () {
    Http::fake([
        'api.easypost.com/v2/insurances' => Http::response([
            'id' => 'ins_test_1',
            'status' => 'new',
            'provider' => 'EasyPost',
            'fee' => ['amount' => '0.50'],
        ], 200),
    ]);

    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();

    $res = $this->actingAs($admin)->postJson('/api/insurance', [
        'tracking_code' => 'EZ123ABC',
        'carrier' => 'USPS',
        'amount_cents' => 10000,
    ]);
    $res->assertStatus(201);
    expect($res->json('ep_insurance_id'))->toBe('ins_test_1');

    expect(Insurance::where('tracking_code', 'EZ123ABC')->first()->fee_cents)->toBe(50);
});

it('surfaces EP failure in messages when package past pre_transit', function () {
    Http::fake([
        'api.easypost.com/v2/insurances' => Http::response([
            'error' => ['code' => 'INSURANCE.AMOUNT.INVALID', 'message' => 'Shipment is already in transit'],
        ], 422),
    ]);

    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();

    $res = $this->actingAs($admin)->postJson('/api/insurance', [
        'tracking_code' => 'EZLATE001',
        'carrier' => 'USPS',
        'amount_cents' => 5000,
    ]);

    $res->assertStatus(201);
    expect($res->json('status'))->toBe('failed');
    expect($res->json('messages.error'))->toContain('422');
});

it('shipper cannot add insurance', function () {
    $shipper = User::where('email', 'pat@shipdesk.local')->firstOrFail();

    $this->actingAs($shipper)->postJson('/api/insurance', [
        'tracking_code' => 'EZ1',
        'carrier' => 'USPS',
        'amount_cents' => 100,
    ])->assertStatus(403);
});

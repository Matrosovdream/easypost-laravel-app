<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Cache::flush();
});

it('overview returns aggregates for the seeded dataset', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();
    $res = $this->actingAs($admin)->getJson('/api/analytics/overview');
    $res->assertOk();
    expect($res->json('total_shipments'))->toBeGreaterThan(0);
    expect($res->json('by_status'))->toBeArray();
});

it('carriers endpoint returns per-carrier rollup', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();
    $res = $this->actingAs($admin)->getJson('/api/analytics/carriers');
    $res->assertOk();
    expect($res->json('carriers'))->toBeArray();
});

it('shipper cannot view analytics', function () {
    $shipper = User::where('email', 'pat@shipdesk.local')->firstOrFail();
    $this->actingAs($shipper)->getJson('/api/analytics/overview')->assertStatus(403);
});

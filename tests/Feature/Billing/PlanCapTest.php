<?php

use App\Models\Shipment;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Cache::flush();

    Http::fake([
        'api.easypost.com/v2/shipments/*/buy' => Http::response([
            'id' => 'shp_ok', 'tracking_code' => 'EZOK', 'postage_label' => ['label_url' => 'x'],
        ], 200),
    ]);
});

it('buy returns plan_cap_exceeded when team is over its shipment cap', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();
    $team = Team::findOrFail($admin->current_team_id);
    $team->forceFill(['plan' => 'starter'])->save();

    // Starter cap = 100. Create 100 shipments already this month.
    $shipment = Shipment::withoutGlobalScopes()->where('reference', 'DEMO-1000')->firstOrFail();
    $template = $shipment->toArray();
    unset($template['id'], $template['created_at'], $template['updated_at'], $template['deleted_at']);
    $template['status'] = 'purchased';
    $template['reference'] = null;
    for ($i = 0; $i < 100; $i++) {
        Shipment::withoutGlobalScopes()->create(array_merge($template, [
            'ep_shipment_id' => 'shp_cap_'.$i,
        ]));
    }

    $rateId = $shipment->rates_snapshot[0]['id'];
    $res = $this->actingAs($admin)->postJson("/api/shipments/{$shipment->id}/buy", ['rate_id' => $rateId]);

    $res->assertOk();
    expect($res->json('status'))->toBe('plan_cap_exceeded');
});

it('billing/plan returns usage + cap', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();
    $res = $this->actingAs($admin)->getJson('/api/billing/plan');
    $res->assertOk();
    expect($res->json('plan'))->toBeString();
    expect($res->json('usage'))->toHaveKeys(['used', 'cap', 'remaining', 'reset_at']);
});

it('checkout returns simulated URL when BILLING_PRICE_* is a placeholder', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();
    $res = $this->actingAs($admin)->postJson('/api/billing/checkout', ['plan' => 'business']);
    $res->assertOk();
    expect($res->json('simulated'))->toBeTrue();
    expect($res->json('url'))->toContain('checkout=simulated');
});

it('shipper cannot start checkout (no billing.manage right)', function () {
    $shipper = User::where('email', 'pat@shipdesk.local')->firstOrFail();
    $this->actingAs($shipper)->postJson('/api/billing/checkout', ['plan' => 'team'])->assertStatus(403);
});

<?php

use App\Models\Claim;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Cache::flush();

    Http::fake([
        'api.easypost.com/v2/claims' => Http::response([
            'id' => 'clm_test_1',
            'status' => 'submitted',
        ], 200),
    ]);
});

it('cs agent can open a claim', function () {
    $csAgent = User::where('email', 'maya@shipdesk.local')->firstOrFail();
    $shipment = Shipment::withoutGlobalScopes()->where('status', 'purchased')->firstOrFail();

    $res = $this->actingAs($csAgent)->postJson('/api/claims', [
        'shipment_id' => $shipment->id,
        'type' => 'damage',
        'amount_cents' => 5000,
        'description' => 'Damaged during transit',
    ]);

    $res->assertStatus(201);
    expect($res->json('state'))->toBe('open');
});

it('submit transitions state to submitted and records ep_claim_id', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();
    $claim = Claim::where('state', 'open')->first() ?? Claim::create([
        'team_id' => $admin->current_team_id,
        'shipment_id' => Shipment::withoutGlobalScopes()->first()->id,
        'type' => 'damage',
        'amount_cents' => 5000,
        'description' => 'Test',
        'state' => 'open',
        'timeline' => [],
    ]);

    $res = $this->actingAs($admin)->postJson("/api/claims/{$claim->id}/submit");
    $res->assertOk();
    expect($res->json('state'))->toBe('submitted');
    expect(Claim::find($claim->id)->ep_claim_id)->toBe('clm_test_1');
});

it('manager can approve a submitted claim', function () {
    $manager = User::where('email', 'riley@shipdesk.local')->firstOrFail();
    $claim = Claim::where('state', 'submitted')->first();
    if (! $claim) {
        $this->markTestSkipped('No submitted claim seeded.');
    }

    $res = $this->actingAs($manager)->postJson("/api/claims/{$claim->id}/approve", [
        'recovered_cents' => 4500,
    ]);

    $res->assertOk();
    expect($res->json('state'))->toBe('approved');
    expect($res->json('recovered_cents'))->toBe(4500);
});

it('viewer cannot open a claim', function () {
    $viewer = User::where('email', 'jordan@shipdesk.local')->firstOrFail();

    $this->actingAs($viewer)->postJson('/api/claims', [
        'shipment_id' => 1,
        'type' => 'damage',
        'amount_cents' => 1000,
        'description' => 'test',
    ])->assertStatus(403);
});

<?php

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
        'api.easypost.com/v2/scan_forms' => Http::response([
            'id' => 'sf_test_1',
            'status' => 'created',
            'form_url' => 'https://easypost-labels.example.com/sf.pdf',
        ], 200),
    ]);
});

it('rejects scan form when carriers differ', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();
    $shipments = Shipment::withoutGlobalScopes()
        ->whereIn('status', ['purchased', 'packed'])
        ->get();

    if ($shipments->count() < 2) {
        $this->markTestSkipped('Need at least 2 shipments seeded.');
    }

    // Force a carrier mismatch on one of them
    $shipments->first()->forceFill(['carrier' => 'UPS'])->save();

    $res = $this->actingAs($admin)->postJson('/api/scan-forms', [
        'shipment_ids' => $shipments->pluck('id')->all(),
    ]);
    $res->assertStatus(422);
    expect($res->json('message'))->toContain('carrier');
});

it('creates a scan form for same-carrier, same-origin shipments', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();
    $shipments = Shipment::withoutGlobalScopes()
        ->whereIn('status', ['purchased', 'packed'])
        ->get();

    $res = $this->actingAs($admin)->postJson('/api/scan-forms', [
        'shipment_ids' => $shipments->pluck('id')->all(),
    ]);

    $res->assertStatus(201);
    expect($res->json('status'))->toBe('created');
    expect($res->json('form_url'))->toBe('https://easypost-labels.example.com/sf.pdf');
});

it('viewer cannot create a scan form', function () {
    $viewer = User::where('email', 'jordan@shipdesk.local')->firstOrFail();

    $this->actingAs($viewer)->postJson('/api/scan-forms', [
        'shipment_ids' => [1],
    ])->assertStatus(403);
});

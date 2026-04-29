<?php

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Cache::flush();
});

it('create + verify returns normalized address marked verified', function () {
    Http::fake([
        'api.easypost.com/v2/addresses' => Http::response([
            'id' => 'adr_test_1',
            'street1' => '1600 Amphitheatre Pkwy',
            'city' => 'Mountain View',
            'state' => 'CA',
            'zip' => '94043-1351',
            'country' => 'US',
            'residential' => true,
            'verifications' => ['delivery' => ['success' => true]],
        ], 200),
    ]);

    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();
    $res = $this->actingAs($admin)->postJson('/api/addresses', [
        'street1' => '1600 amphitheatre pkwy',
        'city' => 'mountain view',
        'state' => 'ca',
        'zip' => '94043',
        'country' => 'US',
        'verify' => true,
    ]);

    $res->assertStatus(201);
    expect($res->json('verified'))->toBeTrue();
    expect($res->json('zip'))->toBe('94043-1351');
    expect($res->json('ep_address_id'))->toBe('adr_test_1');
});

it('viewer cannot create an address', function () {
    $viewer = User::where('email', 'jordan@shipdesk.local')->firstOrFail();
    $this->actingAs($viewer)->postJson('/api/addresses', [
        'street1' => '1 Infinite Loop', 'country' => 'US',
    ])->assertStatus(403);
});

it('delete removes an address', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();

    // Create an isolated address not referenced by any shipment
    $address = Address::create([
        'team_id' => $admin->current_team_id,
        'street1' => '1 Delete Lane',
        'country' => 'US',
        'verified' => false,
    ]);

    $this->actingAs($admin)->deleteJson("/api/addresses/{$address->id}")->assertOk();
    expect(Address::find($address->id))->toBeNull();
});

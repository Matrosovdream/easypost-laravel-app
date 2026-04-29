<?php

use App\Models\Address;
use App\Models\Pickup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Cache::flush();

    Http::fake([
        'api.easypost.com/v2/pickups' => Http::response([
            'id' => 'pu_test_1',
            'status' => 'unknown',
            'pickup_rates' => [
                ['id' => 'pur_1', 'carrier' => 'USPS', 'service' => 'NextDay', 'rate' => '0.00'],
                ['id' => 'pur_2', 'carrier' => 'UPS',  'service' => 'Future', 'rate' => '7.50'],
            ],
        ], 200),
        'api.easypost.com/v2/pickups/*/buy' => Http::response([
            'id' => 'pu_test_1',
            'confirmation' => 'CONF123',
            'rate' => ['carrier' => 'USPS', 'service' => 'NextDay', 'rate' => '0.00'],
        ], 200),
        'api.easypost.com/v2/pickups/*/cancel' => Http::response(['id' => 'pu_test_1', 'status' => 'cancelled'], 200),
    ]);
});

it('schedules a pickup and returns rates', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();
    $address = Address::where('team_id', $admin->current_team_id)->first();
    $address->forceFill(['ep_address_id' => 'adr_test_1'])->save();

    $res = $this->actingAs($admin)->postJson('/api/pickups', [
        'address_id' => $address->id,
        'min_datetime' => now()->addDay()->setTime(13, 0)->toIso8601String(),
        'max_datetime' => now()->addDay()->setTime(17, 0)->toIso8601String(),
        'instructions' => 'back door',
    ]);

    $res->assertStatus(201);
    expect($res->json('status'))->toBe('unknown');
    expect($res->json('rates'))->toHaveCount(2);
});

it('can buy and cancel a pickup', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();

    $pickup = Pickup::create([
        'team_id' => $admin->current_team_id,
        'ep_pickup_id' => 'pu_test_1',
        'address_id' => Address::where('team_id', $admin->current_team_id)->first()->id,
        'min_datetime' => now()->addDay(),
        'max_datetime' => now()->addDay()->addHours(3),
        'status' => 'unknown',
        'created_by' => $admin->id,
    ]);

    $this->actingAs($admin)->postJson("/api/pickups/{$pickup->id}/buy", [
        'carrier' => 'USPS',
        'service' => 'NextDay',
    ])
        ->assertOk()
        ->assertJsonPath('status', 'scheduled')
        ->assertJsonPath('confirmation', 'CONF123');

    $this->actingAs($admin)->postJson("/api/pickups/{$pickup->id}/cancel")
        ->assertOk()
        ->assertJsonPath('status', 'cancelled');
});

it('viewer cannot schedule a pickup', function () {
    $viewer = User::where('email', 'jordan@shipdesk.local')->firstOrFail();

    $this->actingAs($viewer)->postJson('/api/pickups', [
        'address_id' => 1,
        'min_datetime' => now()->addDay()->toIso8601String(),
        'max_datetime' => now()->addDay()->addHours(3)->toIso8601String(),
    ])->assertStatus(403);
});

<?php

use App\Models\Claim;
use App\Models\ReturnRequest;
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
        'api.easypost.com/v2/shipments' => Http::response([
            'id' => 'shp_return_1',
            'rates' => [
                ['id' => 'rate_r1', 'carrier' => 'USPS', 'service' => 'Ground', 'rate' => 6.50],
            ],
        ], 200),
    ]);
});

it('client can submit a return request for their own shipment', function () {
    $client = User::where('email', 'jen@widgets.example.com')->firstOrFail();
    $shipment = Shipment::withoutGlobalScopes()
        ->where('client_id', '!=', null)
        ->where('status', 'delivered')
        ->first();
    if (! $shipment) {
        $this->markTestSkipped('No seeded delivered client shipment.');
    }

    $res = $this->actingAs($client)->postJson('/api/returns', [
        'original_shipment_id' => $shipment->id,
        'reason' => 'wrong_item',
        'notes' => 'Wrong color',
    ]);

    $res->assertStatus(201);
    expect($res->json('status'))->toBe('requested');
});

it('manager can approve a return and a return shipment is created', function () {
    $manager = User::where('email', 'riley@shipdesk.local')->firstOrFail();
    $return = ReturnRequest::where('status', 'requested')->first();
    if (! $return) {
        $this->markTestSkipped('No seeded pending return.');
    }

    $res = $this->actingAs($manager)->postJson("/api/returns/{$return->id}/approve");
    $res->assertOk();
    expect($res->json('status'))->toBe('approved');
    expect($res->json('return_shipment_id'))->toBeInt();
});

it('viewer cannot approve returns', function () {
    $viewer = User::where('email', 'jordan@shipdesk.local')->firstOrFail();
    $return = ReturnRequest::first();
    if (! $return) {
        $this->markTestSkipped('No seeded return.');
    }

    $this->actingAs($viewer)->postJson("/api/returns/{$return->id}/approve")
        ->assertStatus(403);
});

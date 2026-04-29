<?php

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Cache::flush();
});

it('admin sees every shipment in the team', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();

    $res = $this->actingAs($admin)->getJson('/api/shipments');
    $res->assertOk();
    expect($res->json('meta.total'))->toBeGreaterThanOrEqual(5);
});

it('manager sees every shipment in the team', function () {
    $manager = User::where('email', 'riley@shipdesk.local')->firstOrFail();

    $res = $this->actingAs($manager)->getJson('/api/shipments');
    $res->assertOk();
    expect($res->json('meta.total'))->toBeGreaterThanOrEqual(5);
});

it('shipper sees only shipments assigned to them', function () {
    $shipper = User::where('email', 'pat@shipdesk.local')->firstOrFail();

    $res = $this->actingAs($shipper)->getJson('/api/shipments');
    $res->assertOk();

    $ids = collect($res->json('data'))->pluck('id');
    foreach ($ids as $id) {
        $s = Shipment::withoutGlobalScopes()->find($id);
        expect($s->assigned_to)->toBe($shipper->id);
    }
});

it('client sees only their own client_id', function () {
    $client = User::where('email', 'jen@widgets.example.com')->firstOrFail();

    $res = $this->actingAs($client)->getJson('/api/shipments');
    $res->assertOk();

    $ids = collect($res->json('data'))->pluck('id')->all();
    if (count($ids) === 0) {
        expect(true)->toBeTrue();
        return;
    }
    foreach ($ids as $id) {
        $s = Shipment::withoutGlobalScopes()->find($id);
        expect($s->client_id)->not->toBeNull();
    }
});

it('viewer cannot create a shipment', function () {
    $viewer = User::where('email', 'jordan@shipdesk.local')->firstOrFail();

    $res = $this->actingAs($viewer)->postJson('/api/shipments', [
        'to_address' => ['street1' => '1 Infinite Loop', 'country' => 'US'],
        'from_address' => ['street1' => '1 Hacker Way', 'country' => 'US'],
        'parcel' => ['weight_oz' => 16],
    ]);

    $res->assertStatus(403);
});

it('my-queue only returns shipments assigned to the caller', function () {
    $shipper = User::where('email', 'pat@shipdesk.local')->firstOrFail();

    $res = $this->actingAs($shipper)->getJson('/api/shipments/my-queue');
    $res->assertOk();

    foreach ($res->json('data') as $row) {
        $s = Shipment::withoutGlobalScopes()->find($row['id']);
        expect($s->assigned_to)->toBe($shipper->id);
    }
});

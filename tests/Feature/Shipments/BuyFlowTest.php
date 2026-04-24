<?php

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Cache::flush();
});

function fakeEpBuy(): void
{
    Http::fake([
        'api.easypost.com/v2/shipments/*/buy' => Http::response([
            'id' => 'shp_demo_0',
            'tracking_code' => 'EZ9000FAKE',
            'postage_label' => ['label_url' => 'https://easypost-labels.example.com/label.pdf'],
        ], 200),
        'api.easypost.com/v2/shipments/*/refund' => Http::response(['id' => 'shp_demo_0', 'refund_status' => 'submitted'], 200),
    ]);
}

function capShipper(User $shipper, int $capCents): void
{
    DB::table('team_user')
        ->where('team_id', $shipper->current_team_id)
        ->where('user_id', $shipper->id)
        ->update(['spending_cap_cents' => $capCents]);
}

it('admin can buy a rate directly', function () {
    fakeEpBuy();
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();
    $shipment = Shipment::withoutGlobalScopes()->where('reference', 'DEMO-1000')->firstOrFail();
    $rateId = $shipment->rates_snapshot[0]['id'];

    $res = $this->actingAs($admin)->postJson("/api/shipments/{$shipment->id}/buy", ['rate_id' => $rateId]);

    $res->assertOk();
    expect($res->json('status'))->toBe('purchased');
    expect(Shipment::withoutGlobalScopes()->find($shipment->id)->status)->toBe('purchased');
});

it('shipper above spending cap creates an approval instead of buying', function () {
    fakeEpBuy();
    $shipper = User::where('email', 'pat@shipdesk.local')->firstOrFail();
    $shipment = Shipment::withoutGlobalScopes()->where('reference', 'DEMO-1000')->firstOrFail();
    $shipment->forceFill(['assigned_to' => $shipper->id])->save();
    capShipper($shipper, 500); // below $9.95 rate = 995 cents

    $rateId = $shipment->rates_snapshot[0]['id'];

    $res = $this->actingAs($shipper)->postJson("/api/shipments/{$shipment->id}/buy", ['rate_id' => $rateId]);

    $res->assertOk();
    expect($res->json('status'))->toBe('approval_required');
    expect($res->json('approval_id'))->toBeInt();
    expect(Shipment::withoutGlobalScopes()->find($shipment->id)->status)->toBe('pending_approval');
});

it('manager can approve an approval and the label is bought', function () {
    fakeEpBuy();
    $shipper = User::where('email', 'pat@shipdesk.local')->firstOrFail();
    $manager = User::where('email', 'riley@shipdesk.local')->firstOrFail();

    $shipment = Shipment::withoutGlobalScopes()->where('reference', 'DEMO-1000')->firstOrFail();
    $shipment->forceFill(['assigned_to' => $shipper->id])->save();
    capShipper($shipper, 500);

    $rateId = $shipment->rates_snapshot[0]['id'];

    $this->actingAs($shipper)->postJson("/api/shipments/{$shipment->id}/buy", ['rate_id' => $rateId])->assertOk();

    $approvalId = $this->actingAs($manager)
        ->getJson('/api/shipments/approvals')
        ->assertOk()
        ->json('data.0.id');

    $res = $this->actingAs($manager)->postJson("/api/shipments/approvals/{$approvalId}/approve");
    $res->assertOk();
    expect($res->json('status'))->toBe('approved');
    expect($res->json('buy_status'))->toBe('purchased');

    expect(Shipment::withoutGlobalScopes()->find($shipment->id)->status)->toBe('purchased');
});

it('manager can decline an approval with a reason', function () {
    fakeEpBuy();
    $shipper = User::where('email', 'pat@shipdesk.local')->firstOrFail();
    $manager = User::where('email', 'riley@shipdesk.local')->firstOrFail();

    $shipment = Shipment::withoutGlobalScopes()->where('reference', 'DEMO-1000')->firstOrFail();
    $shipment->forceFill(['assigned_to' => $shipper->id])->save();
    capShipper($shipper, 500);

    $this->actingAs($shipper)
        ->postJson("/api/shipments/{$shipment->id}/buy", ['rate_id' => $shipment->rates_snapshot[0]['id']])
        ->assertOk();

    $approvalId = $this->actingAs($manager)->getJson('/api/shipments/approvals')->json('data.0.id');

    $this->actingAs($manager)
        ->postJson("/api/shipments/approvals/{$approvalId}/decline", ['reason' => 'cost too high'])
        ->assertOk()
        ->assertJson(['status' => 'declined']);

    expect(Shipment::withoutGlobalScopes()->find($shipment->id)->status)->toBe('rate_declined');
});

it('admin can void a purchased shipment', function () {
    fakeEpBuy();
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();
    $shipment = Shipment::withoutGlobalScopes()->where('reference', 'DEMO-1002')->firstOrFail(); // purchased

    $res = $this->actingAs($admin)->postJson("/api/shipments/{$shipment->id}/void", ['reason' => 'wrong address']);
    $res->assertOk();
    expect(Shipment::withoutGlobalScopes()->find($shipment->id)->status)->toBe('voided');
});

it('shipper can mark an assigned purchased shipment as packed', function () {
    $shipper = User::where('email', 'pat@shipdesk.local')->firstOrFail();
    $shipment = Shipment::withoutGlobalScopes()->where('reference', 'DEMO-1002')->firstOrFail();
    $shipment->forceFill(['assigned_to' => $shipper->id])->save();

    $res = $this->actingAs($shipper)->postJson("/api/shipments/{$shipment->id}/pack");
    $res->assertOk();
    expect(Shipment::withoutGlobalScopes()->find($shipment->id)->status)->toBe('packed');
});

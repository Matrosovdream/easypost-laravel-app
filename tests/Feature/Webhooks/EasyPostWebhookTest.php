<?php

use App\Events\ShipmentUpdated;
use App\Events\TrackerUpdated;
use App\Models\Shipment;
use App\Models\Tracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Cache::flush();
    Config::set('services.easypost.webhook_secret', 'test-secret');
});

function signedEpPayload(array $payload): array
{
    $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
    $sig = hash_hmac('sha256', $body, 'test-secret');
    return [$body, $sig];
}

it('rejects unsigned webhook with 401 and records signature_valid=false', function () {
    $payload = ['id' => 'evt_badsig', 'description' => 'tracker.updated', 'result' => ['id' => 'trk_x', 'tracking_code' => 'NA']];

    $res = $this->call('POST', '/rest/webhooks/easypost', [], [], [], [
        'HTTP_ACCEPT' => 'application/json',
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_HMAC_SIGNATURE' => 'totally-wrong',
    ], json_encode($payload));

    $res->assertStatus(401);
    expect(DB::table('webhook_events')->where('ep_event_id', 'evt_badsig')->first()->signature_valid)->toBeFalse();
});

it('accepts a valid signature and logs the event as signature_valid=true', function () {
    Event::fake();

    $payload = [
        'id' => 'evt_ok_1',
        'description' => 'tracker.updated',
        'result' => [
            'id' => 'trk_from_webhook',
            'tracking_code' => 'EZ_WH_1',
            'carrier' => 'USPS',
            'status' => 'in_transit',
            'status_detail' => 'arrived_at_facility',
        ],
    ];
    [$body, $sig] = signedEpPayload($payload);

    $res = $this->call('POST', '/rest/webhooks/easypost', [], [], [], [
        'HTTP_ACCEPT' => 'application/json',
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_HMAC_SIGNATURE' => $sig,
    ], $body);

    $res->assertOk();
    expect(DB::table('webhook_events')->where('ep_event_id', 'evt_ok_1')->first()->signature_valid)->toBeTrue();
});

it('tracker.updated upserts Tracker row + appends event + fires broadcasts', function () {
    Event::fake([TrackerUpdated::class, ShipmentUpdated::class]);

    $shipment = Shipment::withoutGlobalScopes()->whereNotNull('tracking_code')->firstOrFail();
    $shipment->forceFill(['tracking_code' => 'EZ2000000000'])->save();

    $payload = [
        'id' => 'evt_tracker_1',
        'description' => 'tracker.updated',
        'result' => [
            'id' => 'trk_live_1',
            'tracking_code' => 'EZ2000000000',
            'carrier' => 'USPS',
            'status' => 'in_transit',
            'tracking_details' => [[
                'status' => 'in_transit',
                'status_detail' => 'arrived_at_facility',
                'message' => 'Arrived at facility',
                'datetime' => now()->subMinutes(5)->toIso8601String(),
                'source' => 'USPS',
                'tracking_location' => ['city' => 'Oakland', 'state' => 'CA', 'country' => 'US'],
            ]],
        ],
    ];
    [$body, $sig] = signedEpPayload($payload);

    $res = $this->call('POST', '/rest/webhooks/easypost', [], [], [], [
        'HTTP_ACCEPT' => 'application/json',
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_HMAC_SIGNATURE' => $sig,
    ], $body);

    $res->assertOk();
    $tracker = Tracker::where('tracking_code', 'EZ2000000000')->first();
    expect($tracker)->not->toBeNull();
    expect($tracker->status)->toBe('in_transit');
    expect($tracker->events()->count())->toBe(1);

    Event::assertDispatched(TrackerUpdated::class);
});

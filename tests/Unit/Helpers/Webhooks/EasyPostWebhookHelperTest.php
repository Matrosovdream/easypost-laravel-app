<?php

use App\Helpers\Webhooks\EasyPostWebhookHelper;
use App\Models\Shipment;
use App\Models\Tracker;
use App\Repositories\Shipping\ShipmentRepo;
use App\Repositories\Tracker\TrackerRepo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->shipments = mock(ShipmentRepo::class);
    $this->trackers = mock(TrackerRepo::class);
    $this->helper = new EasyPostWebhookHelper($this->shipments, $this->trackers);
});

it('isValidSignature returns false when secret is empty', function () {
    config()->set('services.easypost.webhook_secret', '');
    expect($this->helper->isValidSignature('body', 'sig'))->toBeFalse();
});

it('isValidSignature uses HMAC-SHA256 and timing-safe compare', function () {
    config()->set('services.easypost.webhook_secret', 'shhh');
    $body = '{"a":1}';
    $sig = hash_hmac('sha256', $body, 'shhh');

    expect($this->helper->isValidSignature($body, $sig))->toBeTrue();
    expect($this->helper->isValidSignature($body, 'wrong'))->toBeFalse();
});

it('resolveTeamId resolves shipment by EP id (shp_ prefix)', function () {
    $shipment = new Shipment(['team_id' => 7]);
    $this->shipments->shouldReceive('findByEpShipmentId')
        ->with('shp_abc')->once()->andReturn($shipment);

    expect($this->helper->resolveTeamId(['id' => 'shp_abc']))->toBe(7);
});

it('resolveTeamId resolves tracker by EP id (trk_ prefix)', function () {
    $tracker = new Tracker(['team_id' => 9]);
    $this->trackers->shouldReceive('findByEpIdOrCode')
        ->with('trk_x', null)->once()->andReturn($tracker);

    expect($this->helper->resolveTeamId(['id' => 'trk_x']))->toBe(9);
});

it('resolveTeamId falls back to tracking_code lookup', function () {
    $tracker = new Tracker(['team_id' => 11]);
    $this->trackers->shouldReceive('getByTrackingCode')
        ->with('TC1')->once()->andReturn($tracker);

    expect($this->helper->resolveTeamId(['tracking_code' => 'TC1']))->toBe(11);
});

it('resolveTeamId returns null when nothing matches', function () {
    expect($this->helper->resolveTeamId([]))->toBeNull();
});

it('buildEventRow filters out null values', function () {
    $row = $this->helper->buildEventRow(null, 'evt_1', 'tracker.updated', true, ['x' => 1]);
    expect($row)->not->toHaveKey('team_id');
    expect($row)->not->toHaveKey('error');
    expect($row['source'])->toBe('easypost');
    expect($row['ep_event_id'])->toBe('evt_1');
});

it('buildEventRow includes error when set', function () {
    $row = $this->helper->buildEventRow(7, 'evt_2', 'unknown', false, [], 'Bad signature');
    expect($row)->toMatchArray([
        'team_id' => 7,
        'signature_valid' => false,
        'error' => 'Bad signature',
    ]);
});

it('buildEventRow truncates description to 64 chars', function () {
    $row = $this->helper->buildEventRow(null, 'evt_3', str_repeat('A', 200), true, []);
    expect(strlen($row['description']))->toBe(64);
});

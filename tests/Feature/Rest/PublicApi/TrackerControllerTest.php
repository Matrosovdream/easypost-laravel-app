<?php

use App\Models\ContactSubmission;
use App\Models\Team;
use App\Models\Tracker;
use App\Models\TrackerEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Cache::flush();
});

function makeTrackerRecord(array $overrides = []): Tracker
{
    $team = Team::create([
        'name' => 'Demo Tenant',
        'plan' => 'team',
        'status' => 'active',
        'settings' => [
            'brand_name' => 'Demo Tenant',
            'brand_color' => '#10b981',
        ],
    ]);

    $tracker = Tracker::create(array_merge([
        'team_id' => $team->id,
        'ep_tracker_id' => 'trk_'.uniqid(),
        'tracking_code' => 'EZ2000000002',
        'carrier' => 'USPS',
        'status' => 'in_transit',
        'status_detail' => 'arrived_at_facility',
        'est_delivery_date' => now()->addDays(3),
        'is_return' => false,
    ], $overrides));

    TrackerEvent::create([
        'tracker_id' => $tracker->id,
        'status' => 'pre_transit',
        'status_detail' => 'label_created',
        'message' => 'Label created',
        'source' => 'EasyPost',
        'event_datetime' => now()->subDays(1),
        'location' => ['city' => 'Oakland', 'state' => 'CA', 'country' => 'US'],
    ]);

    TrackerEvent::create([
        'tracker_id' => $tracker->id,
        'status' => 'in_transit',
        'status_detail' => 'arrived_at_facility',
        'message' => 'Arrived at facility',
        'source' => 'EasyPost',
        'event_datetime' => now()->subHours(3),
        'location' => ['city' => 'Sacramento', 'state' => 'CA', 'country' => 'US'],
    ]);

    return $tracker;
}

it('returns 404 for unknown tracking code', function () {
    $this->getJson('/rest/public/trackers/DOESNOTEXIST')
        ->assertStatus(404)
        ->assertJson(['message' => 'Not found.']);
});

it('returns tenant branding + events for a valid tracking code', function () {
    makeTrackerRecord();

    $res = $this->getJson('/rest/public/trackers/EZ2000000002');

    $res->assertOk();
    expect($res->json('code'))->toBe('EZ2000000002');
    expect($res->json('carrier'))->toBe('USPS');
    expect($res->json('status'))->toBe('in_transit');
    expect($res->json('status_label'))->toBe('In transit');
    expect($res->json('tenant.name'))->toBe('Demo Tenant');
    expect($res->json('tenant.brand_color'))->toBe('#10b981');
    expect($res->json('events'))->toHaveCount(2);
    expect($res->json('events.0.location'))->toBe('Sacramento, CA, US');
});

it('falls back to team name when no brand_name in settings', function () {
    $team = Team::create(['name' => 'NoBrand LLC', 'plan' => 'starter', 'status' => 'active']);
    Tracker::create([
        'team_id' => $team->id,
        'ep_tracker_id' => 'trk_nobrand',
        'tracking_code' => 'EZXNOBRAND',
        'carrier' => 'UPS',
        'status' => 'pre_transit',
    ]);

    $this->getJson('/rest/public/trackers/EZXNOBRAND')
        ->assertOk()
        ->assertJsonPath('tenant.name', 'NoBrand LLC')
        ->assertJsonPath('tenant.brand_color', null);
});

it('rate-limits at 60 requests per 10 minutes per IP', function () {
    makeTrackerRecord();

    for ($i = 0; $i < 60; $i++) {
        $this->getJson('/rest/public/trackers/EZ2000000002')->assertOk();
    }

    $this->getJson('/rest/public/trackers/EZ2000000002')->assertStatus(429);
});

it('accepts a valid contact submission and persists it', function () {
    $res = $this->postJson('/rest/public/contact', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'company' => 'Acme Corp',
        'topic' => 'sales',
        'message' => 'Hey, what is your volume pricing?',
    ]);

    $res->assertStatus(201);
    expect($res->json('message'))->toBe('Thanks — we received your message.');
    expect(ContactSubmission::count())->toBe(1);
    expect(ContactSubmission::first()->email)->toBe('jane@example.com');
    expect(ContactSubmission::first()->status)->toBe('new');
});

it('validates contact submission fields', function () {
    $this->postJson('/rest/public/contact', [
        'name' => '',
        'email' => 'not-an-email',
        'message' => 'hi',
    ])->assertStatus(422);
});

it('rate-limits contact submissions at 5 per 10 minutes', function () {
    $valid = [
        'name' => 'Jane',
        'email' => 'j@x.com',
        'topic' => 'sales',
        'message' => 'Just saying hello from the test suite.',
    ];

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/rest/public/contact', $valid)->assertStatus(201);
    }

    $this->postJson('/rest/public/contact', $valid)->assertStatus(429);
});

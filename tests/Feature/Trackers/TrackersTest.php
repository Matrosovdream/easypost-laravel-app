<?php

use App\Models\Tracker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Cache::flush();

    Http::fake([
        'api.easypost.com/v2/trackers' => Http::response([
            'id' => 'trk_test_1',
            'status' => 'in_transit',
            'status_detail' => 'arrived_at_facility',
            'public_url' => 'https://track.example.com/EZ2000000002',
            'tracking_details' => [
                ['status' => 'pre_transit', 'status_detail' => 'label_created', 'message' => 'Label created',
                 'datetime' => now()->subDay()->toIso8601String(),
                 'tracking_location' => ['city' => 'Oakland', 'state' => 'CA', 'country' => 'US']],
            ],
        ], 200),
        'api.easypost.com/v2/trackers/*' => Http::response(['id' => 'trk_test_1', 'message' => 'deleted'], 200),
    ]);
});

it('admin can create a standalone tracker', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();
    $res = $this->actingAs($admin)->postJson('/api/trackers', [
        'tracking_code' => 'EZ2000000002',
        'carrier' => 'USPS',
    ]);
    $res->assertStatus(201);
    expect($res->json('status'))->toBe('in_transit');
});

it('admin can delete a tracker and it returns 404 afterwards', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();
    $tracker = Tracker::create([
        'team_id' => $admin->current_team_id,
        'ep_tracker_id' => 'trk_delete_me',
        'tracking_code' => 'EZDEL',
        'carrier' => 'USPS',
        'status' => 'in_transit',
    ]);

    $this->actingAs($admin)->deleteJson("/api/trackers/{$tracker->id}")->assertOk();
    $this->actingAs($admin)->getJson("/api/trackers/{$tracker->id}")->assertStatus(404);
});

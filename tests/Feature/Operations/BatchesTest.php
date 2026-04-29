<?php

use App\Models\Batch;
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
        'api.easypost.com/v2/batches' => Http::response(['id' => 'batch_test_1', 'state' => 'creating', 'status' => []], 200),
        'api.easypost.com/v2/batches/*/buy' => Http::response(['id' => 'batch_test_1', 'state' => 'purchasing'], 200),
        'api.easypost.com/v2/batches/*/label' => Http::response(['id' => 'batch_test_1', 'label_url' => 'https://easypost-labels.example.com/b.pdf'], 200),
    ]);
});

it('admin can list batches', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();

    $res = $this->actingAs($admin)->getJson('/api/batches');
    $res->assertOk();
    expect($res->json('data'))->toBeArray();
});

it('admin can create a batch of purchased shipments', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();
    $shipments = Shipment::withoutGlobalScopes()
        ->whereIn('status', ['purchased', 'packed'])
        ->get();
    expect($shipments->count())->toBeGreaterThan(0);

    $res = $this->actingAs($admin)->postJson('/api/batches', [
        'shipment_ids' => $shipments->pluck('id')->all(),
        'reference' => 'TEST-WAVE',
    ]);

    $res->assertStatus(201);
    expect($res->json('state'))->toBe('creating');
    $id = $res->json('id');

    $batch = Batch::findOrFail($id);
    expect($batch->num_shipments)->toBe($shipments->count());
    expect($batch->ep_batch_id)->toBe('batch_test_1');
});

it('viewer cannot create a batch', function () {
    $viewer = User::where('email', 'jordan@shipdesk.local')->firstOrFail();

    $this->actingAs($viewer)->postJson('/api/batches', [
        'shipment_ids' => [1],
    ])->assertStatus(403);
});

it('admin can buy a batch then generate labels', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();

    $batch = Batch::create([
        'team_id' => $admin->current_team_id,
        'ep_batch_id' => 'batch_test_1',
        'reference' => 'PRESEED',
        'state' => 'created',
        'num_shipments' => 2,
        'created_by' => $admin->id,
    ]);

    $this->actingAs($admin)->postJson("/api/batches/{$batch->id}/buy")
        ->assertOk()
        ->assertJsonPath('state', 'purchasing');

    $this->actingAs($admin)->postJson("/api/batches/{$batch->id}/labels")
        ->assertOk()
        ->assertJsonPath('label_url', 'https://easypost-labels.example.com/b.pdf');
});

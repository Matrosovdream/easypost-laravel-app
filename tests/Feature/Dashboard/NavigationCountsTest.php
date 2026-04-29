<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Cache::flush();
});

it('requires authentication', function () {
    $this->getJson('/api/navigation/counts')->assertStatus(401);
});

it('returns counts shape for a freshly seeded admin', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();

    $res = $this->actingAs($admin)->getJson('/api/navigation/counts');
    $res->assertOk();
    $data = $res->json();
    expect($data)->toHaveKeys(['approvalsCount', 'exceptionsCount', 'returnsCount', 'claimsCount', 'queueCount', 'printReady']);
    expect($data['approvalsCount'])->toBe(0);
    // DemoShipmentsSeeder creates one 'purchased' shipment that is not packed yet:
    expect($data['printReady'])->toBeGreaterThanOrEqual(1);
});

it('shipper sees printReady counter', function () {
    $shipper = User::where('email', 'pat@shipdesk.local')->firstOrFail();

    $res = $this->actingAs($shipper)->getJson('/api/navigation/counts');
    $res->assertOk();
    expect($res->json('printReady'))->toBeGreaterThanOrEqual(0);
});

it('access-requests endpoint persists a request', function () {
    $shipper = User::where('email', 'pat@shipdesk.local')->firstOrFail();

    $res = $this->actingAs($shipper)->postJson('/api/access-requests', [
        'requested_permission' => 'billing.manage',
        'target_url' => '/dashboard/settings/billing',
    ]);

    $res->assertStatus(201);
    expect($res->json('id'))->toBeInt();

    $this->assertDatabaseHas('access_requests', [
        'user_id' => $shipper->id,
        'requested_permission' => 'billing.manage',
        'status' => 'pending',
    ]);
});

it('access-requests requires authentication', function () {
    $this->postJson('/api/access-requests', [
        'requested_permission' => 'billing.manage',
    ])->assertStatus(401);
});

it('access-requests validates permission field', function () {
    $shipper = User::where('email', 'pat@shipdesk.local')->firstOrFail();

    $this->actingAs($shipper)->postJson('/api/access-requests', [])->assertStatus(422);
});

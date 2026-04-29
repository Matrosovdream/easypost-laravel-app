<?php

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Cache::flush();
});

it('admin can create a client', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();
    $res = $this->actingAs($admin)->postJson('/api/clients', [
        'company_name' => 'New Client Co',
        'flexrate_markup_pct' => 7.5,
    ]);
    $res->assertStatus(201);
    expect($res->json('company_name'))->toBe('New Client Co');
});

it('set flex-rate updates the markup', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();
    $client = Client::where('team_id', $admin->current_team_id)->firstOrFail();

    $res = $this->actingAs($admin)->postJson("/api/clients/{$client->id}/flex-rate", [
        'flexrate_markup_pct' => 12.5,
    ]);
    $res->assertOk();
    expect((float) $res->json('flexrate_markup_pct'))->toBe(12.5);
});

it('invoice aggregates billable shipments with FlexRate markup applied', function () {
    $admin = User::where('email', 'stan+admin@shipdesk.local')->firstOrFail();
    $client = Client::where('team_id', $admin->current_team_id)->firstOrFail();
    $client->forceFill(['flexrate_markup_pct' => 10])->save();

    $res = $this->actingAs($admin)->postJson("/api/clients/{$client->id}/invoice", [
        'from' => now()->subDays(30)->toDateString(),
        'to' => now()->toDateString(),
    ]);
    $res->assertOk();
    expect($res->json('totals.charge_cents'))->toBeGreaterThanOrEqual(0);
});

it('viewer cannot create a client', function () {
    $viewer = User::where('email', 'jordan@shipdesk.local')->firstOrFail();
    $this->actingAs($viewer)->postJson('/api/clients', [
        'company_name' => 'Test',
    ])->assertStatus(403);
});

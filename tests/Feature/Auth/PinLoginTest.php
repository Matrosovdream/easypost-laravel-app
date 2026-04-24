<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Cache::flush();
});

function pinHash(string $pin): string
{
    return hash_hmac('sha256', $pin, config('app.pin_pepper'));
}

it('logs in with a valid admin PIN and returns /dashboard redirect', function () {
    $response = $this->postJson('/api/auth/pin-login', ['pin' => '9999']);

    $response->assertOk();
    expect($response->json('redirect'))->toBe('/dashboard');
    expect($response->json('user.email'))->toBe('stan+admin@shipdesk.local');
    expect($response->json('user.roles'))->toHaveCount(1);
    expect($response->json('user.roles.0.slug'))->toBe('admin');
    expect($response->json('user.permissions'))->toContain('dashboard.view');
    expect($response->json('user.permissions'))->toContain('billing.manage');

    $this->assertAuthenticated();
});

it('logs in with shipper PIN and has restricted permissions', function () {
    $response = $this->postJson('/api/auth/pin-login', ['pin' => '7777']);

    $response->assertOk();
    expect($response->json('user.roles.0.slug'))->toBe('shipper');
    expect($response->json('user.permissions'))->toContain('shipments.view.assigned');
    expect($response->json('user.permissions'))->not->toContain('billing.manage');
    expect($response->json('user.permissions'))->not->toContain('shipments.approve');
});

it('logs in with client PIN and has own-scoped permissions', function () {
    $response = $this->postJson('/api/auth/pin-login', ['pin' => '5555']);

    $response->assertOk();
    expect($response->json('user.roles.0.slug'))->toBe('client');
    expect($response->json('user.permissions'))->toContain('shipments.view.own');
    expect($response->json('user.permissions'))->not->toContain('shipments.view.any');
});

it('rejects an invalid PIN with 422', function () {
    $response = $this->postJson('/api/auth/pin-login', ['pin' => '0000']);

    $response->assertStatus(422);
    expect($response->json('message'))->toBe('Invalid PIN.');
    $this->assertGuest();
});

it('validates the PIN field is required', function () {
    $this->postJson('/api/auth/pin-login', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['pin']);
});

it('rate-limits after 3 failed attempts on the same PIN', function () {
    // Per-PIN lock fires after 3 failures on that hash.
    for ($i = 0; $i < 3; $i++) {
        $this->postJson('/api/auth/pin-login', ['pin' => '0001'])->assertStatus(422);
    }

    // 4th attempt on the same PIN → locked
    $this->postJson('/api/auth/pin-login', ['pin' => '0001'])->assertStatus(429);
});

it('IP-level rate limiter locks after 5 failures across different PINs', function () {
    foreach (['0001', '0002', '0003', '0004', '0005'] as $pin) {
        $this->postJson('/api/auth/pin-login', ['pin' => $pin])->assertStatus(422);
    }

    // 6th request from same IP on a different PIN → IP-level lock
    $this->postJson('/api/auth/pin-login', ['pin' => '0006'])->assertStatus(429);
});

it('rejects PIN login for inactive users', function () {
    User::where('email', 'stan+admin@shipdesk.local')->update(['is_active' => false]);

    $this->postJson('/api/auth/pin-login', ['pin' => '9999'])->assertStatus(422);
    $this->assertGuest();
});

it('GET /api/auth/me requires authentication', function () {
    $this->getJson('/api/auth/me')->assertStatus(401);
});

it('GET /api/auth/me returns the current user after login', function () {
    $user = User::where('email', 'riley@shipdesk.local')->first();

    $this->actingAs($user)
        ->getJson('/api/auth/me')
        ->assertOk()
        ->assertJsonPath('user.email', 'riley@shipdesk.local')
        ->assertJsonPath('user.roles.0.slug', 'manager');
});

it('logs out endpoint returns redirect and is callable when authenticated', function () {
    $user = User::where('email', 'jordan@shipdesk.local')->first();

    $this->actingAs($user)
        ->postJson('/api/auth/logout')
        ->assertOk()
        ->assertJsonPath('redirect', '/portal/login');
});

it('touches last_login_at on successful login', function () {
    User::where('email', 'stan+admin@shipdesk.local')
        ->update(['last_login_at' => null]);

    $this->postJson('/api/auth/pin-login', ['pin' => '9999'])->assertOk();

    $user = User::where('email', 'stan+admin@shipdesk.local')->first();
    expect($user->last_login_at)->not->toBeNull();
});

it('writes an auth.pin_login audit_logs row on success', function () {
    $this->postJson('/api/auth/pin-login', ['pin' => '6666'])->assertOk();

    $row = \DB::table('audit_logs')
        ->where('action', 'auth.pin_login')
        ->orderByDesc('id')
        ->first();

    expect($row)->not->toBeNull();
    expect($row->subject_type)->toBe(User::class);
});

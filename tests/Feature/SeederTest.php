<?php

use App\Models\Role;
use App\Models\RoleRight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

it('seeds the six system roles', function () {
    expect(Role::where('is_system', true)->count())->toBe(6);
    $expected = ['admin', 'manager', 'shipper', 'cs_agent', 'client', 'viewer'];
    foreach ($expected as $slug) {
        expect(Role::where('slug', $slug)->exists())->toBeTrue("missing role {$slug}");
    }
});

it('seeds role rights', function () {
    expect(RoleRight::count())->toBeGreaterThan(100);

    $adminRightsCount = RoleRight::where(
        'role_id', Role::where('slug', 'admin')->value('id'),
    )->count();
    expect($adminRightsCount)->toBeGreaterThan(30);
});

it('seeds 12 demo users with PIN hashes', function () {
    expect(User::whereNotNull('pin_hash')->count())->toBe(12);
});

it('seeded PINs resolve to the expected roles', function () {
    $cases = [
        ['9999', 'admin'],
        ['8888', 'manager'],
        ['7777', 'shipper'],
        ['6666', 'cs_agent'],
        ['5555', 'client'],
        ['4444', 'viewer'],
    ];

    foreach ($cases as [$pin, $expectedRole]) {
        $hash = hash_hmac('sha256', $pin, config('app.pin_pepper'));
        $user = User::where('pin_hash', $hash)->first();
        expect($user)->not->toBeNull("PIN {$pin} resolved no user");

        $user->load('roles');
        $slugs = $user->roles->pluck('slug')->all();
        expect($slugs)->toContain($expectedRole);
    }
});

it('pin hashes are unique across all users', function () {
    $hashes = User::whereNotNull('pin_hash')->pluck('pin_hash');
    expect($hashes)->toHaveCount(12);
    expect($hashes->unique())->toHaveCount(12);
});

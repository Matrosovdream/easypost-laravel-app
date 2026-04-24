<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Cache::flush();
});

it('change PIN rotates own hash and invalidates the old PIN', function () {
    $shipper = User::where('email', 'pat@shipdesk.local')->firstOrFail();
    $pepper = config('app.pin_pepper');

    $oldPin = '7777';
    $newPin = '1234';

    // Ensure the known seeded PIN
    $shipper->forceFill(['pin_hash' => hash_hmac('sha256', $oldPin, $pepper)])->save();

    $res = $this->actingAs($shipper)->postJson('/api/profile/pin', [
        'current_pin' => $oldPin,
        'new_pin' => $newPin,
        'new_pin_confirmation' => $newPin,
    ]);
    $res->assertOk();

    expect($shipper->fresh()->pin_hash)->toBe(hash_hmac('sha256', $newPin, $pepper));
});

it('change PIN rejects wrong current PIN', function () {
    $shipper = User::where('email', 'pat@shipdesk.local')->firstOrFail();

    $res = $this->actingAs($shipper)->postJson('/api/profile/pin', [
        'current_pin' => '0000',
        'new_pin' => '2222',
        'new_pin_confirmation' => '2222',
    ]);
    $res->assertStatus(422);
});

it('change PIN rejects a PIN already in use', function () {
    $shipper = User::where('email', 'pat@shipdesk.local')->firstOrFail();
    $pepper = config('app.pin_pepper');
    $shipper->forceFill(['pin_hash' => hash_hmac('sha256', '7777', $pepper)])->save();

    // 9999 is admin's PIN — collision
    $res = $this->actingAs($shipper)->postJson('/api/profile/pin', [
        'current_pin' => '7777',
        'new_pin' => '9999',
        'new_pin_confirmation' => '9999',
    ]);
    $res->assertStatus(422);
});

it('update profile saves name', function () {
    $shipper = User::where('email', 'pat@shipdesk.local')->firstOrFail();
    $this->actingAs($shipper)->putJson('/api/profile', ['name' => 'Patrick Shipper'])->assertOk();
    expect($shipper->fresh()->name)->toBe('Patrick Shipper');
});

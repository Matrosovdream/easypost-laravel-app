<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Cache::flush();
});

function loginAndMe(string $pin): array
{
    $login = test()->postJson('/api/auth/pin-login', ['pin' => $pin])->assertOk();
    $me = test()->getJson('/api/auth/me')->assertOk();

    return [
        'login' => $login->json(),
        'me' => $me->json(),
    ];
}

it('admin PIN 9999 resolves to admin role in /auth/me', function () {
    [$login, $me] = array_values(loginAndMe('9999'));

    expect($login['user']['roles'][0]['slug'])->toBe('admin')
        ->and($me['user']['roles'][0]['slug'])->toBe('admin')
        ->and($me['user']['permissions'])->toContain('billing.manage')
        ->and($me['user']['permissions'])->toContain('users.manage');
});

it('manager PIN 8888 resolves to manager role with approve right', function () {
    $me = loginAndMe('8888')['me'];

    expect($me['user']['roles'][0]['slug'])->toBe('manager')
        ->and($me['user']['permissions'])->toContain('shipments.approve')
        ->and($me['user']['permissions'])->not->toContain('billing.manage');
});

it('shipper PIN 7777 can print but cannot approve', function () {
    $me = loginAndMe('7777')['me'];

    expect($me['user']['roles'][0]['slug'])->toBe('shipper')
        ->and($me['user']['permissions'])->toContain('labels.print')
        ->and($me['user']['permissions'])->not->toContain('shipments.approve')
        ->and($me['user']['permissions'])->not->toContain('users.manage');
});

it('cs agent PIN 6666 sees returns + claims rights', function () {
    $me = loginAndMe('6666')['me'];

    expect($me['user']['roles'][0]['slug'])->toBe('cs_agent')
        ->and($me['user']['permissions'])->toContain('returns.view.any')
        ->and($me['user']['permissions'])->toContain('claims.view');
});

it('client PIN 5555 is scoped and cannot approve', function () {
    $me = loginAndMe('5555')['me'];

    expect($me['user']['roles'][0]['slug'])->toBe('client')
        ->and($me['user']['permissions'])->not->toContain('shipments.approve')
        ->and($me['user']['permissions'])->not->toContain('users.manage');
});

it('viewer PIN 4444 is read-only', function () {
    $me = loginAndMe('4444')['me'];

    expect($me['user']['roles'][0]['slug'])->toBe('viewer')
        ->and($me['user']['permissions'])->not->toContain('shipments.approve')
        ->and($me['user']['permissions'])->not->toContain('labels.print')
        ->and($me['user']['permissions'])->not->toContain('users.manage');
});

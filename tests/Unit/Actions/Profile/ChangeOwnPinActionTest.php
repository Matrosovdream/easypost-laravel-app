<?php

use App\Actions\Profile\ChangeOwnPinAction;
use App\Models\User;
use App\Repositories\Infra\AuditLogRepo;
use App\Repositories\User\UserRepo;

beforeEach(function () {
    config()->set('app.pin_pepper', 'test-pepper-32-bytes-min-test-pepper');
    $this->users = mock(UserRepo::class);
    $this->audit = mock(AuditLogRepo::class);
    $this->action = new ChangeOwnPinAction($this->users, $this->audit);
});

function changeOwnPinHash(string $pin): string {
    return hash_hmac('sha256', $pin, config('app.pin_pepper'));
}

it('throws when current PIN is wrong', function () {
    $user = new User();
    $user->id = 1;
    $user->pin_hash = changeOwnPinHash('1111');

    expect(fn () => $this->action->execute($user, '9999', '2222'))
        ->toThrow(RuntimeException::class, 'Current PIN is incorrect.');
});

it('throws when new PIN equals old', function () {
    $user = new User();
    $user->id = 1;
    $user->pin_hash = changeOwnPinHash('1111');

    expect(fn () => $this->action->execute($user, '1111', '1111'))
        ->toThrow(RuntimeException::class);
});

it('throws when new PIN is already in use by another user', function () {
    $user = new User();
    $user->id = 1;
    $user->current_team_id = 3;
    $user->pin_hash = changeOwnPinHash('1111');

    $this->users->shouldReceive('pinHashInUseByOther')->andReturn(true);

    expect(fn () => $this->action->execute($user, '1111', '2222'))
        ->toThrow(RuntimeException::class, 'already in use');
});

it('persists new hash and writes audit on success', function () {
    $user = new User();
    $user->id = 1;
    $user->current_team_id = 3;
    $user->pin_hash = changeOwnPinHash('1111');

    $this->users->shouldReceive('pinHashInUseByOther')->andReturn(false);
    $this->users->shouldReceive('setPinHash')->with(1, changeOwnPinHash('2222'))->once();
    $this->audit->shouldReceive('record')
        ->withArgs(fn ($payload) => $payload['action'] === 'auth.pin.changed')
        ->once();

    expect($this->action->execute($user, '1111', '2222'))->toBe(['ok' => true]);
});

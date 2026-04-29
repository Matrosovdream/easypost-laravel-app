<?php

use App\Actions\Auth\PinLoginAction;
use App\Helpers\Auth\AuthHelper;
use App\Models\User;
use App\Repositories\Infra\AuditLogRepo;
use App\Repositories\User\UserRepo;
use Illuminate\Http\Request;

beforeEach(function () {
    config()->set('app.pin_pepper', 'test-pepper-32-bytes-min-test-pepper');
    $this->users = mock(UserRepo::class);
    $this->audit = mock(AuditLogRepo::class);
    $this->action = new PinLoginAction($this->users, $this->audit, new AuthHelper());
});

it('returns null when no user matches the PIN hash', function () {
    $this->users->shouldReceive('getByPinHash')->andReturn(null);
    $request = Request::create('/x', 'POST');

    expect($this->action->execute('1234', $request))->toBeNull();
});

it('returns null when user has no active team membership', function () {
    $user = new User();
    $user->id = 1;
    $this->users->shouldReceive('getByPinHash')->andReturn(['id' => 1, 'Model' => $user]);
    $this->users->shouldReceive('hasActiveTeamMembership')->with(1)->andReturn(false);

    $request = Request::create('/x', 'POST');
    expect($this->action->execute('1234', $request))->toBeNull();
});

it('throws when PIN_PEPPER is not configured', function () {
    config()->set('app.pin_pepper', '');

    $request = Request::create('/x', 'POST');
    expect(fn () => $this->action->execute('1234', $request))
        ->toThrow(RuntimeException::class, 'PIN_PEPPER');
});

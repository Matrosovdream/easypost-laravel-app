<?php

use App\Actions\Users\RegenerateUserPinAction;
use App\Models\User;
use App\Repositories\Infra\AuditLogRepo;
use App\Repositories\User\UserRepo;

beforeEach(function () {
    config()->set('app.pin_pepper', 'test-pepper-32-bytes-min-test-pepper');
    $this->users = mock(UserRepo::class);
    $this->audit = mock(AuditLogRepo::class);
    $this->action = new RegenerateUserPinAction($this->users, $this->audit);
});

it('aborts 403 when actor lacks users.manage', function () {
    $actor = mock(User::class)->makePartial();
    $actor->shouldReceive('rights')->andReturn([]);

    expect(fn () => $this->action->execute($actor, 99))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('aborts 404 when target user not found', function () {
    $actor = mock(User::class)->makePartial();
    $actor->shouldReceive('rights')->andReturn(['users.manage']);

    $this->users->shouldReceive('getModel')->andReturn(new class {
        public function newQuery() { return new class {
            public function find($id) { return null; }
        }; }
    });

    expect(fn () => $this->action->execute($actor, 99))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('returns a fresh 4-digit PIN and writes audit', function () {
    $actor = mock(User::class)->makePartial();
    $actor->shouldReceive('rights')->andReturn(['users.manage']);

    $target = new User();
    $target->id = 5;
    $target->current_team_id = 3;

    $this->users->shouldReceive('getModel')->andReturn(new class($target) {
        public function __construct(private $t) {}
        public function newQuery() { return new class($this->t) {
            public function __construct(private $t) {}
            public function find($id) { return $this->t; }
        }; }
    });
    $this->users->shouldReceive('pinHashInUseByOther')->andReturn(false);
    $this->users->shouldReceive('setPinHash')->once();
    $this->audit->shouldReceive('record')
        ->withArgs(fn ($payload) => $payload['action'] === 'auth.pin.regenerated')
        ->once();

    $out = $this->action->execute($actor, 5);
    expect($out)->toHaveKey('pin');
    expect($out['pin'])->toMatch('/^\d{4}$/');
});

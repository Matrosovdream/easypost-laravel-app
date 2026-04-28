<?php

use App\Actions\Users\SetUserActiveAction;
use App\Models\User;
use App\Repositories\User\UserRepo;

beforeEach(function () {
    $this->users = mock(UserRepo::class);
    $this->action = new SetUserActiveAction($this->users);
});

it('aborts 403 when user lacks users.manage', function () {
    $actor = mock(User::class)->makePartial();
    $actor->shouldReceive('rights')->andReturn([]);

    expect(fn () => $this->action->execute($actor, 99, false))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('aborts 422 when actor tries to disable themselves', function () {
    $actor = mock(User::class)->makePartial();
    $actor->shouldReceive('rights')->andReturn(['users.manage']);
    $actor->id = 7;

    expect(fn () => $this->action->execute($actor, 7, false))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('allows actor to enable themselves', function () {
    $actor = mock(User::class)->makePartial();
    $actor->shouldReceive('rights')->andReturn(['users.manage']);
    $actor->id = 7;

    $target = new User();
    $target->id = 7;

    $modelQuery = $this->mock(\stdClass::class);
    $this->users->shouldReceive('getModel')->andReturn(new class {
        public function newQuery() {
            return new class {
                public function find($id) {
                    $u = new \App\Models\User();
                    $u->id = $id;
                    return $u;
                }
            };
        }
    });
    $this->users->shouldReceive('setActive')->with(7, true)->once();

    expect($this->action->execute($actor, 7, true))->toBe(['ok' => true]);
});

it('aborts 404 when target user not found', function () {
    $actor = mock(User::class)->makePartial();
    $actor->shouldReceive('rights')->andReturn(['users.manage']);
    $actor->id = 7;

    $this->users->shouldReceive('getModel')->andReturn(new class {
        public function newQuery() {
            return new class {
                public function find($id) { return null; }
            };
        }
    });

    expect(fn () => $this->action->execute($actor, 99, false))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

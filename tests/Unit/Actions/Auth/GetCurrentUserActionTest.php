<?php

use App\Actions\Auth\GetCurrentUserAction;
use App\Models\User;
use App\Repositories\User\UserRepo;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->users = mock(UserRepo::class);
    $this->action = new GetCurrentUserAction($this->users);
});

it('aborts 401 when no authenticated user is present', function () {
    $request = Request::create('/x', 'GET');
    expect(fn () => $this->action->execute($request))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('returns user envelope for authenticated request', function () {
    $user = mock(User::class)->makePartial();
    $user->id = 1;
    $user->shouldReceive('loadMissing')->andReturnSelf();
    $user->shouldReceive('rights')->andReturn(['x']);
    $user->setRelation('roles', collect());
    $user->setRelation('currentTeam', null);
    $user->phone = null; $user->avatar_s3_key = null; $user->locale = null;
    $user->timezone = null; $user->is_active = true; $user->last_login_at = null;
    $user->created_at = null;

    $this->users->shouldReceive('getByID')
        ->with(1)
        ->andReturn(['id' => 1, 'email' => 'x@x.test', 'name' => 'X', 'Model' => $user]);

    $request = Request::create('/x', 'GET');
    $request->setUserResolver(fn () => $user);

    expect($this->action->execute($request))->toHaveKey('user');
});

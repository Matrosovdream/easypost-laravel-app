<?php

use App\Actions\Auth\LogoutAction;
use App\Helpers\Auth\AuthHelper;
use App\Models\User;
use App\Repositories\Infra\AuditLogRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->auditLogs = mock(AuditLogRepo::class);
    $this->action = new LogoutAction($this->auditLogs, new AuthHelper());
});

it('records logout audit when a user is authenticated', function () {
    $user = new User();
    $user->id = 1;
    $user->current_team_id = 3;
    Auth::shouldReceive('user')->andReturn($user);
    Auth::shouldReceive('guard')->andReturnSelf();
    Auth::shouldReceive('logout')->byDefault();

    $this->auditLogs->shouldReceive('record')
        ->withArgs(fn ($payload) => $payload['action'] === 'auth.logout' && $payload['user_id'] === 1)
        ->once();

    $request = Request::create('/x', 'POST');
    $this->action->execute($request);
});

it('skips audit when no user is authenticated', function () {
    Auth::shouldReceive('user')->andReturn(null);

    $this->auditLogs->shouldNotReceive('record');

    $request = Request::create('/x', 'POST');
    $this->action->execute($request);
});

<?php

use App\Helpers\Auth\AuthHelper;
use App\Models\User;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->helper = new AuthHelper();
});

it('builds a standard auth audit payload', function () {
    $user = new User(['name' => 'Stan']);
    $user->id = 7;
    $user->current_team_id = 3;

    $request = Request::create('/x', 'POST', server: [
        'REMOTE_ADDR' => '10.0.0.1',
        'HTTP_USER_AGENT' => 'TestAgent',
    ]);

    $out = $this->helper->buildAuditPayload($user, 'auth.login', $request, ['method' => 'pin']);

    expect($out)->toMatchArray([
        'team_id' => 3,
        'user_id' => 7,
        'action' => 'auth.login',
        'subject_type' => User::class,
        'subject_id' => 7,
        'ip' => '10.0.0.1',
        'user_agent' => 'TestAgent',
    ]);
    expect(json_decode($out['meta'], true))->toBe(['method' => 'pin']);
});

it('truncates user_agent to 255 chars', function () {
    $user = new User();
    $user->id = 1;
    $user->current_team_id = 1;

    $request = Request::create('/x', 'POST', server: [
        'HTTP_USER_AGENT' => str_repeat('A', 500),
    ]);

    $out = $this->helper->buildAuditPayload($user, 'auth.x', $request);
    expect(strlen($out['user_agent']))->toBe(255);
});

it('clearSession is a no-op when request has no session', function () {
    $request = Request::create('/x', 'POST');
    expect(fn () => $this->helper->clearSession($request))->not->toThrow(\Throwable::class);
});

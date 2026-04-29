<?php

use App\Actions\Profile\ListSessionsAction;
use Illuminate\Http\Request;

it('returns one session shape with current=true', function () {
    $action = new ListSessionsAction();
    $request = Request::create('/x', 'GET', server: [
        'REMOTE_ADDR' => '10.0.0.1',
        'HTTP_USER_AGENT' => 'TestUA',
    ]);
    $request->setLaravelSession(app('session.store'));

    $out = $action->execute($request);
    expect($out)->toHaveKey('data');
    expect($out['data'])->toHaveCount(1);
    expect($out['data'][0]['current'])->toBeTrue();
    expect($out['data'][0]['ip'])->toBe('10.0.0.1');
    expect($out['data'][0]['user_agent'])->toBe('TestUA');
});

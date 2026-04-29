<?php

use App\Actions\Auth\PinLoginAction;
use App\Actions\Auth\PinLoginRateLimitedAction;
use App\Helpers\Auth\PinLoginRateLimiterHelper;
use App\Models\User;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Http\Request;

function makeMappedUser(): array
{
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('loadMissing')->andReturnSelf();
    $user->shouldReceive('rights')->andReturn([]);
    $user->setRelation('roles', collect());
    $user->setRelation('currentTeam', null);
    $user->phone = null;
    $user->avatar_s3_key = null;
    $user->locale = null;
    $user->timezone = null;
    $user->is_active = true;
    $user->last_login_at = null;
    $user->created_at = null;

    return ['id' => 1, 'email' => 'a@b.test', 'name' => 'Stan', 'Model' => $user];
}

beforeEach(function () {
    $this->login = mock(PinLoginAction::class);
    $this->limiter = new PinLoginRateLimiterHelper(
        new RateLimiter(new CacheRepository(new ArrayStore()))
    );
    $this->action = new PinLoginRateLimitedAction($this->login, $this->limiter);
    $this->request = Request::create('/x', 'POST', server: ['REMOTE_ADDR' => '10.0.0.1']);
});

it('returns 422 envelope when login fails', function () {
    $this->login->shouldReceive('execute')->andReturn(null);

    $out = $this->action->execute($this->request, '1234');

    expect($out['_status'])->toBe(422);
    expect($out['body']['message'])->toBe('Invalid PIN.');
});

it('returns 200 envelope with user + redirect on success', function () {
    $this->login->shouldReceive('execute')->andReturn(makeMappedUser());

    $out = $this->action->execute($this->request, '1234');

    expect($out['_status'])->toBe(200);
    expect($out['body'])->toHaveKeys(['user', 'redirect']);
    expect($out['body']['redirect'])->toBe('/dashboard');
});

it('returns 429 envelope when PIN bucket is exhausted', function () {
    $this->login->shouldReceive('execute')->andReturn(null);

    $this->action->execute($this->request, '1234');
    $this->action->execute($this->request, '1234');
    $this->action->execute($this->request, '1234');

    $out = $this->action->execute($this->request, '1234');
    expect($out['_status'])->toBe(429);
    expect($out['body']['message'])->toContain('PIN');
});

it('clears rate-limit buckets on successful login', function () {
    $this->login->shouldReceive('execute')->once()->andReturn(null);
    $this->action->execute($this->request, '1234');

    $this->login->shouldReceive('execute')->once()->andReturn(makeMappedUser());
    $out = $this->action->execute($this->request, '1234');
    expect($out['_status'])->toBe(200);
});

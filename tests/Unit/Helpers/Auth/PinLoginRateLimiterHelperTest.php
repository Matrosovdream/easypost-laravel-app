<?php

use App\Helpers\Auth\PinLoginRateLimiterHelper;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Cache\ArrayStore;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->limiter = new RateLimiter(new CacheRepository(new ArrayStore()));
    $this->helper = new PinLoginRateLimiterHelper($this->limiter);
    $this->request = Request::create('/x', 'POST', server: ['REMOTE_ADDR' => '10.0.0.1']);
});

it('preflight returns null when no attempts have been made', function () {
    expect($this->helper->preflight($this->request, '1234'))->toBeNull();
});

it('preflight returns 429 payload after PIN bucket exhausted', function () {
    $this->helper->recordFailure($this->request, '1234');
    $this->helper->recordFailure($this->request, '1234');
    $this->helper->recordFailure($this->request, '1234');

    $blocked = $this->helper->preflight($this->request, '1234');

    expect($blocked)->not->toBeNull();
    expect($blocked['message'])->toContain('PIN');
    expect($blocked['retry_after'])->toBeGreaterThan(0);
});

it('preflight returns 429 IP-level after 5 failures across different PINs', function () {
    foreach (['1111', '2222', '3333', '4444', '5555'] as $pin) {
        $this->helper->recordFailure($this->request, $pin);
    }

    $blocked = $this->helper->preflight($this->request, '6666');
    expect($blocked)->not->toBeNull();
    expect($blocked['message'])->toContain('Too many attempts');
});

it('clear resets both buckets', function () {
    $this->helper->recordFailure($this->request, '1234');
    $this->helper->recordFailure($this->request, '1234');
    $this->helper->recordFailure($this->request, '1234');
    expect($this->helper->preflight($this->request, '1234'))->not->toBeNull();

    $this->helper->clear($this->request, '1234');
    expect($this->helper->preflight($this->request, '1234'))->toBeNull();
});

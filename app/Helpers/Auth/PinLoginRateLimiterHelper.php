<?php

namespace App\Helpers\Auth;

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;

class PinLoginRateLimiterHelper
{
    private const IP_MAX_ATTEMPTS = 5;
    private const PIN_MAX_ATTEMPTS = 3;
    private const IP_DECAY_SECONDS = 600;
    private const PIN_DECAY_SECONDS = 300;

    public function __construct(
        private readonly RateLimiter $limiter,
    ) {}

    /**
     * Returns a 429 payload + retry-after if either bucket is exhausted, or null to proceed.
     */
    public function preflight(Request $request, string $pin): ?array
    {
        $ipKey = $this->ipKey($request);
        $pinKey = $this->pinKey($pin);

        if ($this->limiter->tooManyAttempts($ipKey, self::IP_MAX_ATTEMPTS)) {
            return [
                'message' => 'Too many attempts. Try again later.',
                'retry_after' => $this->limiter->availableIn($ipKey),
            ];
        }
        if ($this->limiter->tooManyAttempts($pinKey, self::PIN_MAX_ATTEMPTS)) {
            return [
                'message' => 'This PIN is temporarily locked. Try again later.',
                'retry_after' => $this->limiter->availableIn($pinKey),
            ];
        }

        return null;
    }

    public function recordFailure(Request $request, string $pin): void
    {
        $this->limiter->hit($this->ipKey($request), self::IP_DECAY_SECONDS);
        $this->limiter->hit($this->pinKey($pin), self::PIN_DECAY_SECONDS);
    }

    public function clear(Request $request, string $pin): void
    {
        $this->limiter->clear($this->ipKey($request));
        $this->limiter->clear($this->pinKey($pin));
    }

    private function ipKey(Request $request): string
    {
        return 'pin-login:ip:'.$request->ip();
    }

    private function pinKey(string $pin): string
    {
        return 'pin-login:pin:'.hash('sha256', $pin);
    }
}

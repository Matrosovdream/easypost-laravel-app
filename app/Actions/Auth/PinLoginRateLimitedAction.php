<?php

namespace App\Actions\Auth;

use App\Helpers\Auth\PinLoginRateLimiterHelper;
use App\Http\Resources\Api\UserResource;
use Illuminate\Http\Request;

class PinLoginRateLimitedAction
{
    public function __construct(
        private readonly PinLoginAction $login,
        private readonly PinLoginRateLimiterHelper $limiter,
    ) {}

    /**
     * Returns either a successful login envelope or an error envelope with `_status`
     * indicating which HTTP status the controller should emit.
     */
    public function execute(Request $request, string $pin): array
    {
        $blocked = $this->limiter->preflight($request, $pin);
        if ($blocked !== null) {
            return ['_status' => 429, 'body' => $blocked];
        }

        $result = $this->login->execute(pin: $pin, request: $request);

        if (! $result) {
            $this->limiter->recordFailure($request, $pin);
            return ['_status' => 422, 'body' => ['message' => 'Invalid PIN.']];
        }

        $this->limiter->clear($request, $pin);

        return [
            '_status' => 200,
            'body' => [
                'user' => UserResource::make($result)->resolve($request),
                'redirect' => '/dashboard',
            ],
        ];
    }
}

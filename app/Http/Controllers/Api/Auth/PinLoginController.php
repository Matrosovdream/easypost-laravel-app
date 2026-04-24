<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\PinLoginAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\PinLoginRequest;
use App\Http\Resources\Api\UserResource;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;

final class PinLoginController extends Controller
{
    public function __invoke(
        PinLoginRequest $request,
        PinLoginAction $action,
        RateLimiter $limiter,
    ): JsonResponse {
        $ipKey  = 'pin-login:ip:'.$request->ip();
        $pinKey = 'pin-login:pin:'.hash('sha256', (string) $request->input('pin'));

        if ($limiter->tooManyAttempts($ipKey, maxAttempts: 5)) {
            return response()->json([
                'message' => 'Too many attempts. Try again later.',
                'retry_after' => $limiter->availableIn($ipKey),
            ], 429);
        }
        if ($limiter->tooManyAttempts($pinKey, maxAttempts: 3)) {
            return response()->json([
                'message' => 'This PIN is temporarily locked. Try again later.',
                'retry_after' => $limiter->availableIn($pinKey),
            ], 429);
        }

        $result = $action->execute(
            pin:     (string) $request->input('pin'),
            request: $request,
        );

        if (! $result) {
            $limiter->hit($ipKey, decaySeconds: 600);
            $limiter->hit($pinKey, decaySeconds: 300);
            return response()->json([
                'message' => 'Invalid PIN.',
            ], 422);
        }

        $limiter->clear($ipKey);
        $limiter->clear($pinKey);

        return response()->json([
            'user'     => UserResource::make($result)->resolve($request),
            'redirect' => '/dashboard',
        ]);
    }
}

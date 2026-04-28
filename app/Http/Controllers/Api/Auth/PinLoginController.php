<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\PinLoginRateLimitedAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\PinLoginRequest;
use Illuminate\Http\JsonResponse;

final class PinLoginController extends Controller
{
    public function __invoke(PinLoginRequest $request, PinLoginRateLimitedAction $action): JsonResponse
    {
        $result = $action->execute($request, (string) $request->input('pin'));

        return response()->json($result['body'], $result['_status']);
    }
}

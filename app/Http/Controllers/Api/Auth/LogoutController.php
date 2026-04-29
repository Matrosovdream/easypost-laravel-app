<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\LogoutAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LogoutController extends Controller
{
    public function __invoke(Request $request, LogoutAction $action): JsonResponse
    {
        $action->execute($request);

        return response()->json(['redirect' => '/portal/login']);
    }
}

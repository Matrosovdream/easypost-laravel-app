<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\GetCurrentUserAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MeController extends Controller
{
    public function __invoke(Request $request, GetCurrentUserAction $action): JsonResponse
    {
        return response()->json($action->execute($request));
    }
}

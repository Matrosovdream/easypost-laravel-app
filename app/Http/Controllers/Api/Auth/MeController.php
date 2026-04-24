<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Repositories\User\UserRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MeController extends Controller
{
    public function __invoke(Request $request, UserRepo $users): JsonResponse
    {
        $user = $request->user();
        abort_if(! $user, 401);

        $mapped = $users->getByID($user->id);

        return response()->json([
            'user' => UserResource::make($mapped)->resolve($request),
        ]);
    }
}

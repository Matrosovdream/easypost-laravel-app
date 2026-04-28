<?php

namespace App\Http\Controllers\Api\Settings;

use App\Actions\Users\ChangeUserRoleAction;
use App\Actions\Users\InviteUserAction;
use App\Actions\Users\ListTeamUsersAction;
use App\Actions\Users\RegenerateUserPinAction;
use App\Actions\Users\SetUserActiveAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Settings\ChangeRoleRequest;
use App\Http\Requests\Api\Settings\InviteUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function __construct(
        private readonly ListTeamUsersAction $list,
        private readonly InviteUserAction $invite,
        private readonly ChangeUserRoleAction $changeRole,
        private readonly SetUserActiveAction $setActive,
        private readonly RegenerateUserPinAction $regenPin,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->list->execute($request->user()));
    }

    public function invite(InviteUserRequest $request): JsonResponse
    {
        return response()->json($this->invite->execute($request->user(), $request->validated()), 201);
    }

    public function changeRole(ChangeRoleRequest $request, int $id): JsonResponse
    {
        try {
            return response()->json($this->changeRole->execute(
                $request->user(),
                $id,
                $request->validated()['role_slug'],
            ));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function disable(Request $request, int $id): JsonResponse
    {
        return response()->json($this->setActive->execute($request->user(), $id, false));
    }

    public function enable(Request $request, int $id): JsonResponse
    {
        return response()->json($this->setActive->execute($request->user(), $id, true));
    }

    public function regeneratePin(Request $request, int $id): JsonResponse
    {
        return response()->json($this->regenPin->execute($request->user(), $id));
    }
}

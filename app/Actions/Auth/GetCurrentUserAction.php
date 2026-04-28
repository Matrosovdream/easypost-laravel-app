<?php

namespace App\Actions\Auth;

use App\Http\Resources\Api\UserResource;
use App\Repositories\User\UserRepo;
use Illuminate\Http\Request;

class GetCurrentUserAction
{
    public function __construct(
        private readonly UserRepo $users,
    ) {}

    public function execute(Request $request): array
    {
        $user = $request->user();
        abort_if(! $user, 401);

        $mapped = $this->users->getByID($user->id);

        return [
            'user' => UserResource::make($mapped)->resolve($request),
        ];
    }
}

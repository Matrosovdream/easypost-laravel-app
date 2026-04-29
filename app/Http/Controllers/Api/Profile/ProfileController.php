<?php

namespace App\Http\Controllers\Api\Profile;

use App\Actions\Profile\ChangeOwnPinAction;
use App\Actions\Profile\GetNotificationPrefsAction;
use App\Actions\Profile\ListSessionsAction;
use App\Actions\Profile\UpdateNotificationPrefsAction;
use App\Actions\Profile\UpdateProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Profile\ChangePinRequest;
use App\Http\Requests\Api\Profile\UpdateNotificationsRequest;
use App\Http\Requests\Api\Profile\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UpdateProfileAction $update,
        private readonly ChangeOwnPinAction $changePin,
        private readonly ListSessionsAction $sessions,
        private readonly GetNotificationPrefsAction $getNotifs,
        private readonly UpdateNotificationPrefsAction $updateNotifs,
    ) {}

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        return response()->json($this->update->execute($request->user(), $request->validated()));
    }

    public function changePin(ChangePinRequest $request): JsonResponse
    {
        $v = $request->validated();
        try {
            return response()->json($this->changePin->execute($request->user(), $v['current_pin'], $v['new_pin']));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function sessions(Request $request): JsonResponse
    {
        return response()->json($this->sessions->execute($request));
    }

    public function notifications(Request $request): JsonResponse
    {
        return response()->json($this->getNotifs->execute($request->user()));
    }

    public function updateNotifications(UpdateNotificationsRequest $request): JsonResponse
    {
        return response()->json($this->updateNotifs->execute($request->user(), $request->validated()['prefs']));
    }
}

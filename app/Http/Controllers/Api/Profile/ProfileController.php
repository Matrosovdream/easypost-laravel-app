<?php

namespace App\Http\Controllers\Api\Profile;

use App\Actions\Profile\ChangeOwnPinAction;
use App\Actions\Profile\UpdateProfileAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UpdateProfileAction $update,
        private readonly ChangeOwnPinAction $changePin,
    ) {}

    public function update(Request $request): JsonResponse
    {
        $v = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:24'],
            'locale' => ['sometimes', 'string', 'max:16'],
            'timezone' => ['sometimes', 'string', 'max:64'],
        ]);
        $this->update->execute($request->user(), $v);
        return response()->json(['ok' => true]);
    }

    public function changePin(Request $request): JsonResponse
    {
        $v = $request->validate([
            'current_pin' => ['required', 'string', 'between:4,8'],
            'new_pin' => ['required', 'string', 'between:4,8', 'different:current_pin'],
            'new_pin_confirmation' => ['required', 'same:new_pin'],
        ]);

        try {
            $this->changePin->execute($request->user(), $v['current_pin'], $v['new_pin']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['ok' => true]);
    }

    public function sessions(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [[
                'id' => $request->session()?->getId() ?? 'current',
                'user_agent' => substr((string) $request->userAgent(), 0, 120),
                'ip' => $request->ip(),
                'last_activity' => now()->toIso8601String(),
                'current' => true,
            ]],
        ]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $prefs = $request->user()->notification_prefs ?? [
            'email.shipment.delivered' => true,
            'email.return.status' => true,
            'email.claim.status' => true,
            'email.approval.requested' => true,
        ];
        return response()->json(['data' => $prefs]);
    }

    public function updateNotifications(Request $request): JsonResponse
    {
        $v = $request->validate([
            'prefs' => ['required', 'array'],
        ]);
        // Persisted on users table (requires migration to add); for now accept & echo.
        return response()->json(['data' => $v['prefs']]);
    }
}

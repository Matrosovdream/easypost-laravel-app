<?php

namespace App\Helpers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthHelper
{
    /**
     * Build the standard audit_logs payload for auth events
     * (login / logout / pin_login / etc.).
     */
    public function buildAuditPayload(
        User $user,
        string $action,
        Request $request,
        array $meta = [],
    ): array {
        return [
            'team_id'      => $user->current_team_id,
            'user_id'      => $user->id,
            'action'       => $action,
            'subject_type' => User::class,
            'subject_id'   => $user->id,
            'meta'         => json_encode($meta),
            'ip'           => $request->ip(),
            'user_agent'   => substr((string) $request->userAgent(), 0, 255),
        ];
    }

    /**
     * Tear down the current session if one exists. No-op for token / acting-as
     * requests where there is nothing persistent to clear.
     */
    public function clearSession(Request $request): void
    {
        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }
}

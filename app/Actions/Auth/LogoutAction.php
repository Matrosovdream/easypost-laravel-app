<?php

namespace App\Actions\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class LogoutAction
{
    public function execute(Request $request): void
    {
        $user = Auth::user();

        if ($user) {
            DB::table('audit_logs')->insert([
                'team_id'      => $user->current_team_id,
                'user_id'      => $user->id,
                'action'       => 'auth.logout',
                'subject_type' => \App\Models\User::class,
                'subject_id'   => $user->id,
                'meta'         => json_encode([]),
                'ip'           => $request->ip(),
                'user_agent'   => substr((string) $request->userAgent(), 0, 255),
                'created_at'   => now(),
            ]);
        }

        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        // No session means the caller authenticated via token or acting-as —
        // nothing persistent to clear; the next request naturally starts fresh.
    }
}

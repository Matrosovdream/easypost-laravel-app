<?php

namespace App\Actions\Profile;

use Illuminate\Http\Request;

class ListSessionsAction
{
    public function execute(Request $request): array
    {
        return [
            'data' => [[
                'id' => $request->session()?->getId() ?? 'current',
                'user_agent' => substr((string) $request->userAgent(), 0, 120),
                'ip' => $request->ip(),
                'last_activity' => now()->toIso8601String(),
                'current' => true,
            ]],
        ];
    }
}

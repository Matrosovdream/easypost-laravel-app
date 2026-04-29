<?php

namespace App\Actions\Auth;

use App\Helpers\Auth\AuthHelper;
use App\Repositories\Infra\AuditLogRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class LogoutAction
{
    public function __construct(
        private readonly AuditLogRepo $auditLogs,
        private readonly AuthHelper $authHelper,
    ) {}

    public function execute(Request $request): void
    {
        $user = Auth::user();

        if ($user) {
            $this->auditLogs->record($this->authHelper->buildAuditPayload($user, 'auth.logout', $request));
        }

        $this->authHelper->clearSession($request);
    }
}

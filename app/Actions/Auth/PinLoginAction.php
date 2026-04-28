<?php

namespace App\Actions\Auth;

use App\Helpers\Auth\AuthHelper;
use App\Repositories\Infra\AuditLogRepo;
use App\Repositories\User\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Authenticates a user by their PIN, creating a Sanctum SPA session.
 *
 * - Computes HMAC-SHA256 of the plaintext PIN with PIN_PEPPER.
 * - Looks up the unique `users.pin_hash`. Returns null if no active match.
 * - Ensures the user has at least one active team membership.
 * - Logs in via the default web guard (Sanctum-stateful).
 * - Touches `last_login_at`.
 * - Writes an audit_logs row.
 *
 * Returns a flat array suitable for UserResource, or null on failure.
 */
final class PinLoginAction
{
    public function __construct(
        private readonly UserRepo $users,
        private readonly AuditLogRepo $auditLogs,
        private readonly AuthHelper $authHelper,
    ) {}

    public function execute(string $pin, Request $request): ?array
    {
        $pepper = config('app.pin_pepper');
        if (! is_string($pepper) || $pepper === '') {
            throw new \RuntimeException('PIN_PEPPER is not configured.');
        }

        $hash = hash_hmac('sha256', $pin, $pepper);

        $mapped = $this->users->getByPinHash($hash);
        if (! $mapped) {
            return null;
        }

        /** @var \App\Models\User $user */
        $user = $mapped['Model'];

        if (! $this->users->hasActiveTeamMembership($user->id)) {
            return null;
        }

        Auth::guard('web')->login($user, remember: true);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $this->users->touchLastLoginNow($user->id);

        $this->auditLogs->record(
            $this->authHelper->buildAuditPayload($user, 'auth.pin_login', $request, ['method' => 'pin'])
        );

        return $mapped;
    }
}

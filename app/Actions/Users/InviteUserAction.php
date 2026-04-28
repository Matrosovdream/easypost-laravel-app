<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Repositories\Infra\InvitationRepo;
use App\Repositories\User\RoleRepo;
use Illuminate\Support\Str;

class InviteUserAction
{
    public function __construct(
        private readonly RoleRepo $roles,
        private readonly InvitationRepo $invitations,
    ) {}

    public function execute(User $inviter, array $input): array
    {
        $rights = $inviter->rights();
        abort_unless(in_array('users.invite', $rights, true), 403);

        if ($input['role_slug'] === 'admin' && ! in_array('users.role.assign.admin', $rights, true)) {
            abort(403, 'Only admins may invite other admins.');
        }

        $role = $this->roles->getBySlug($input['role_slug']);
        if (! $role) {
            throw new \RuntimeException("Role '{$input['role_slug']}' not found.");
        }

        $token = Str::random(48);
        $expiresAt = now()->addDays(7);

        $id = $this->invitations->create([
            'team_id' => $inviter->current_team_id,
            'email' => strtolower($input['email']),
            'role_id' => $role['id'],
            'client_id' => $input['client_id'] ?? null,
            'spending_cap_cents' => $input['spending_cap_cents'] ?? null,
            'daily_cap_cents' => $input['daily_cap_cents'] ?? null,
            'token' => $token,
            'expires_at' => $expiresAt,
            'invited_by' => $inviter->id,
            'notes' => $input['notes'] ?? null,
        ]);

        return ['id' => $id, 'token' => $token, 'expires_at' => $expiresAt];
    }
}

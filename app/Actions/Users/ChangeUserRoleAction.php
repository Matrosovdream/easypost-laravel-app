<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Repositories\User\RoleRepo;
use App\Repositories\User\UserRepo;
use RuntimeException;

class ChangeUserRoleAction
{
    public function __construct(
        private readonly RoleRepo $roles,
        private readonly UserRepo $users,
    ) {}

    public function execute(User $actor, int $targetId, string $newRoleSlug): array
    {
        $rights = $actor->rights();
        abort_unless(in_array('users.role.assign', $rights, true), 403);

        if ($newRoleSlug === 'admin' && ! in_array('users.role.assign.admin', $rights, true)) {
            abort(403, 'Only admins may promote to admin.');
        }

        $target = $this->users->getModel()->newQuery()->find($targetId);
        abort_if(! $target, 404);

        $teamId = (int) $actor->current_team_id;

        $newRole = $this->roles->getBySlug($newRoleSlug);
        if (! $newRole) {
            throw new RuntimeException("Role '{$newRoleSlug}' not found.");
        }

        $adminRole = $this->roles->getBySlug('admin');
        $adminRoleId = (int) $adminRole['id'];

        if ($this->users->hasRoleInTeam($target->id, $teamId, $adminRoleId) && $newRoleSlug !== 'admin') {
            $adminCount = $this->users->countAdminsInTeam($teamId, $adminRoleId);
            if ($adminCount <= 1) {
                throw new RuntimeException('Cannot demote the last admin.');
            }
        }

        $this->users->replaceTeamRole($target->id, $teamId, (int) $newRole['id'], $actor->id);

        return ['ok' => true];
    }
}

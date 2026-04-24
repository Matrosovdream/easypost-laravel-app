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

    public function execute(User $actor, User $target, string $newRoleSlug): void
    {
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
    }
}

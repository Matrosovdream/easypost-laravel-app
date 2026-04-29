<?php

namespace App\Actions\Users;

use App\Helpers\Users\UserManagementHelper;
use App\Models\User;
use App\Repositories\User\UserRepo;

class ListPeopleByRoleAction
{
    private const ALLOWED_ROLES = ['manager', 'shipper', 'cs_agent', 'client', 'viewer'];

    public function __construct(
        private readonly UserRepo $users,
        private readonly UserManagementHelper $helper,
    ) {}

    public function execute(User $user, string $roleSlug): array
    {
        abort_unless(in_array('users.role.assign.admin', $user->rights(), true), 403);
        abort_unless(in_array($roleSlug, self::ALLOWED_ROLES, true), 404);

        return $this->helper->toPeopleListPayload(
            $this->users->listUsersByRoleWithStats((int) $user->current_team_id, $roleSlug),
            $roleSlug,
        );
    }
}

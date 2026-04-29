<?php

namespace App\Actions\Users;

use App\Helpers\Users\UserManagementHelper;
use App\Models\User;
use App\Repositories\User\UserRepo;

class ListManagersAction
{
    public function __construct(
        private readonly UserRepo $users,
        private readonly UserManagementHelper $helper,
    ) {}

    public function execute(User $user): array
    {
        abort_unless(in_array('users.role.assign.admin', $user->rights(), true), 403);

        return $this->helper->toManagerListPayload(
            $this->users->listManagersWithStats((int) $user->current_team_id)
        );
    }
}

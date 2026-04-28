<?php

namespace App\Actions\Team;

use App\Helpers\Team\TeamHelper;
use App\Models\User;
use App\Repositories\Team\TeamRepo;

class ShowTeamAction
{
    public function __construct(
        private readonly TeamRepo $teams,
        private readonly TeamHelper $helper,
    ) {}

    public function execute(User $user): array
    {
        $rights = $user->rights();
        abort_unless(
            in_array('settings.team.edit', $rights, true) || in_array('users.manage', $rights, true),
            403,
        );

        $mapped = $this->teams->getByID($user->current_team_id);
        abort_if(! $mapped, 404);

        return $this->helper->toDetail($mapped['Model']);
    }
}

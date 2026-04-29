<?php

namespace App\Actions\Team;

use App\Models\User;
use App\Repositories\Team\TeamRepo;

class UpdateTeamAction
{
    public function __construct(
        private readonly TeamRepo $teams,
    ) {}

    public function execute(User $user, array $input): array
    {
        abort_unless(in_array('settings.team.edit', $user->rights(), true), 403);

        $this->teams->update((int) $user->current_team_id, $input);

        return ['ok' => true];
    }
}

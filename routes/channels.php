<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
 * Private broadcast channel authorization. `team.{id}` is available to any user
 * who has an active team_user membership for that team.
 */
Broadcast::channel('team.{teamId}', function (User $user, int $teamId) {
    return $user->teams()->where('teams.id', $teamId)->exists()
        ? ['id' => $user->id, 'name' => $user->name]
        : false;
});

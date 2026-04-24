<?php

namespace App\Policies;

use App\Models\ReturnRequest;
use App\Models\User;

class ReturnRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) array_intersect(
            $user->rights(),
            ['returns.view.any', 'returns.view.own', 'returns.approve', 'returns.request.any', 'returns.request.own']
        );
    }

    public function view(User $user, ReturnRequest $return): bool
    {
        if ((int) $return->team_id !== (int) $user->current_team_id) return false;

        $rights = $user->rights();
        if (in_array('returns.view.any', $rights, true) || in_array('returns.approve', $rights, true)) {
            return true;
        }
        if (in_array('returns.view.own', $rights, true) || in_array('returns.request.own', $rights, true)) {
            $clientId = $user->teams()->where('teams.id', $user->current_team_id)->first()?->pivot?->client_id;
            return $clientId && (int) $clientId === (int) $return->client_id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return (bool) array_intersect(
            $user->rights(),
            ['returns.request.any', 'returns.request.own']
        );
    }

    public function approve(User $user): bool
    {
        return in_array('returns.approve', $user->rights(), true);
    }
}

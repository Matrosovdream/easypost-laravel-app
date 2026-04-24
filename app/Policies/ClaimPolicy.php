<?php

namespace App\Policies;

use App\Models\Claim;
use App\Models\User;

class ClaimPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array('claims.view', $user->rights(), true);
    }

    public function view(User $user, Claim $claim): bool
    {
        return (int) $claim->team_id === (int) $user->current_team_id
            && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array('claims.open', $user->rights(), true);
    }

    public function approve(User $user, Claim $claim): bool
    {
        return $this->view($user, $claim) && in_array('claims.approve', $user->rights(), true);
    }
}

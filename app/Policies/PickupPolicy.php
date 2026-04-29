<?php

namespace App\Policies;

use App\Models\Pickup;
use App\Models\User;

class PickupPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array('pickups.manage', $user->rights(), true)
            || in_array('shipments.view.any', $user->rights(), true);
    }

    public function view(User $user, Pickup $pickup): bool
    {
        return (int) $pickup->team_id === (int) $user->current_team_id
            && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array('pickups.manage', $user->rights(), true);
    }

    public function cancel(User $user, Pickup $pickup): bool
    {
        return $this->view($user, $pickup) && $this->create($user);
    }
}

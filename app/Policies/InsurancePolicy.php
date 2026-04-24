<?php

namespace App\Policies;

use App\Models\Insurance;
use App\Models\User;

class InsurancePolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) array_intersect($user->rights(), ['insurance.view', 'insurance.add', 'insurance.add.high_value']);
    }

    public function view(User $user, Insurance $insurance): bool
    {
        return (int) $insurance->team_id === (int) $user->current_team_id && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return (bool) array_intersect($user->rights(), ['insurance.add', 'insurance.add.high_value']);
    }
}

<?php

namespace App\Policies;

use App\Models\ScanForm;
use App\Models\User;

class ScanFormPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array('scan_forms.manage', $user->rights(), true)
            || in_array('shipments.view.any', $user->rights(), true);
    }

    public function view(User $user, ScanForm $scanForm): bool
    {
        return (int) $scanForm->team_id === (int) $user->current_team_id
            && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array('scan_forms.manage', $user->rights(), true);
    }
}

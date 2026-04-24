<?php

namespace App\Policies;

use App\Models\Batch;
use App\Models\User;

class BatchPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array('batches.manage', $user->rights(), true)
            || in_array('shipments.view.any', $user->rights(), true);
    }

    public function view(User $user, Batch $batch): bool
    {
        return (int) $batch->team_id === (int) $user->current_team_id
            && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array('batches.manage', $user->rights(), true);
    }

    public function update(User $user, Batch $batch): bool
    {
        return $this->view($user, $batch) && $this->create($user);
    }
}

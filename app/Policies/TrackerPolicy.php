<?php

namespace App\Policies;

use App\Models\Tracker;
use App\Models\User;

class TrackerPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) array_intersect($user->rights(), ['trackers.view.any', 'trackers.view.own']);
    }

    public function view(User $user, Tracker $tracker): bool
    {
        return (int) $tracker->team_id === (int) $user->current_team_id && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array('trackers.create.standalone', $user->rights(), true);
    }

    public function delete(User $user, Tracker $tracker): bool
    {
        return $this->view($user, $tracker) && in_array('trackers.delete', $user->rights(), true);
    }
}

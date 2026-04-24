<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) array_intersect($user->rights(), ['clients.view', 'clients.manage']);
    }

    public function view(User $user, Client $client): bool
    {
        return (int) $client->team_id === (int) $user->current_team_id && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array('clients.manage', $user->rights(), true);
    }

    public function update(User $user, Client $client): bool
    {
        return $this->view($user, $client) && $this->create($user);
    }
}

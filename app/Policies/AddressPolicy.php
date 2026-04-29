<?php

namespace App\Policies;

use App\Models\Address;
use App\Models\User;

class AddressPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) array_intersect($user->rights(), ['addresses.create', 'addresses.verify', 'shipments.view.any', 'shipments.view.assigned', 'shipments.view.own']);
    }

    public function view(User $user, Address $address): bool
    {
        return (int) $address->team_id === (int) $user->current_team_id;
    }

    public function create(User $user): bool
    {
        return in_array('addresses.create', $user->rights(), true);
    }

    public function verify(User $user, Address $address): bool
    {
        return $this->view($user, $address) && in_array('addresses.verify', $user->rights(), true);
    }

    public function delete(User $user, Address $address): bool
    {
        return $this->view($user, $address) && in_array('addresses.create', $user->rights(), true);
    }
}

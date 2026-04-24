<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;

class ShipmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->rights() === [] ? false : true;
    }

    public function view(User $user, Shipment $shipment): bool
    {
        if ((int) $shipment->team_id !== (int) $user->current_team_id) {
            return false;
        }

        $rights = $user->rights();
        if (in_array('shipments.view.any', $rights, true)) {
            return true;
        }
        if (in_array('shipments.view.assigned', $rights, true) && (int) $shipment->assigned_to === (int) $user->id) {
            return true;
        }
        if (in_array('shipments.view.own', $rights, true)) {
            $membership = $user->teams()->where('teams.id', $user->current_team_id)->first()?->pivot;
            return $membership?->client_id && (int) $membership->client_id === (int) $shipment->client_id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $this->any($user, ['shipments.create']);
    }

    public function buy(User $user, Shipment $shipment): bool
    {
        if (! $this->view($user, $shipment)) return false;
        return $this->any($user, ['shipments.buy', 'shipments.approve']);
    }

    public function void(User $user, Shipment $shipment): bool
    {
        if (! $this->view($user, $shipment)) return false;
        return $this->any($user, ['shipments.void']);
    }

    public function assign(User $user, Shipment $shipment): bool
    {
        if (! $this->view($user, $shipment)) return false;
        return $this->any($user, ['shipments.assign']);
    }

    public function pack(User $user, Shipment $shipment): bool
    {
        if (! $this->view($user, $shipment)) return false;
        return $this->any($user, ['labels.print', 'shipments.approve']);
    }

    public function approve(User $user): bool
    {
        return $this->any($user, ['shipments.approve']);
    }

    private function any(User $user, array $rights): bool
    {
        return (bool) array_intersect($rights, $user->rights());
    }
}

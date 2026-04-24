<?php

namespace App\Actions\Pickups;

use App\Models\Pickup;
use App\Models\User;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Operations\PickupRepo;

class CancelPickupAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly PickupRepo $pickups,
    ) {}

    public function execute(User $user, Pickup $pickup): Pickup
    {
        if ($pickup->ep_pickup_id) {
            try {
                $this->ep->cancelPickup($pickup->ep_pickup_id);
            } catch (\Throwable) {
                // ignore network errors; mark cancelled locally anyway
            }
        }
        return $this->pickups->markCancelled($pickup);
    }
}

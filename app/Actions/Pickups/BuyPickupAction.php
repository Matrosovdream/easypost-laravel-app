<?php

namespace App\Actions\Pickups;

use App\Models\Pickup;
use App\Models\User;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Operations\PickupRepo;

class BuyPickupAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly PickupRepo $pickups,
    ) {}

    public function execute(User $user, Pickup $pickup, string $carrier, string $service): Pickup
    {
        if ($pickup->ep_pickup_id) {
            try {
                $resp = $this->ep->buyPickup($pickup->ep_pickup_id, $carrier, $service);
                $this->pickups->markScheduled($pickup, [
                    'carrier' => $carrier,
                    'service' => $service,
                    'confirmation' => $resp['confirmation'] ?? null,
                    'cost_cents' => isset($resp['rate']['rate'])
                        ? (int) round(((float) $resp['rate']['rate']) * 100)
                        : null,
                ]);
            } catch (\Throwable) {
                // leave as-is; retry possible
            }
        } else {
            $this->pickups->markScheduled($pickup, ['carrier' => $carrier, 'service' => $service]);
        }

        return $pickup->fresh();
    }
}

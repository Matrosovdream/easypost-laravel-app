<?php

namespace App\Actions\Pickups;

use App\Helpers\Pickups\PickupHelper;
use App\Models\User;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Operations\PickupRepo;
use Illuminate\Support\Facades\Gate;

class BuyPickupAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly PickupRepo $pickups,
        private readonly PickupHelper $helper,
    ) {}

    public function execute(User $user, int $id, string $carrier, string $service): array
    {
        $pickup = $this->pickups->findWithAddress($id);
        abort_if(! $pickup, 404);
        Gate::authorize('view', $pickup);

        if ($pickup->ep_pickup_id) {
            try {
                $resp = $this->ep->buyPickup($pickup->ep_pickup_id, $carrier, $service);
                $pickup = $this->pickups->markScheduled($pickup, [
                    'carrier' => $carrier,
                    'service' => $service,
                    'confirmation' => $resp['confirmation'] ?? null,
                    'cost_cents' => isset($resp['rate']['rate'])
                        ? (int) round(((float) $resp['rate']['rate']) * 100)
                        : null,
                ]);
            } catch (\Throwable) {
                // leave as-is; retry possible
                $pickup = $pickup->fresh();
            }
        } else {
            $pickup = $this->pickups->markScheduled($pickup, ['carrier' => $carrier, 'service' => $service]);
        }

        return $this->helper->toBuyResult($pickup);
    }
}

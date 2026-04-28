<?php

namespace App\Actions\Pickups;

use App\Helpers\Pickups\PickupHelper;
use App\Models\User;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Operations\PickupRepo;
use Illuminate\Support\Facades\Gate;

class CancelPickupAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly PickupRepo $pickups,
        private readonly PickupHelper $helper,
    ) {}

    public function execute(User $user, int $id): array
    {
        $pickup = $this->pickups->findWithAddress($id);
        abort_if(! $pickup, 404);
        Gate::authorize('cancel', $pickup);

        if ($pickup->ep_pickup_id) {
            try {
                $this->ep->cancelPickup($pickup->ep_pickup_id);
            } catch (\Throwable) {
                // ignore network errors; mark cancelled locally anyway
            }
        }
        $pickup = $this->pickups->markCancelled($pickup);

        return $this->helper->toCancelResult($pickup);
    }
}

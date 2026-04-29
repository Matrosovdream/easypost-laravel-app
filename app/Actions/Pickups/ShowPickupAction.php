<?php

namespace App\Actions\Pickups;

use App\Helpers\Pickups\PickupHelper;
use App\Repositories\Operations\PickupRepo;
use Illuminate\Support\Facades\Gate;

class ShowPickupAction
{
    public function __construct(
        private readonly PickupRepo $pickups,
        private readonly PickupHelper $helper,
    ) {}

    public function execute(int $id): array
    {
        $pickup = $this->pickups->findWithAddress($id);
        abort_if(! $pickup, 404);
        Gate::authorize('view', $pickup);

        return $this->helper->toDetail($pickup);
    }
}

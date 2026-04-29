<?php

namespace App\Actions\Shipments;

use App\Helpers\Shipments\ShipmentListPayloadHelper;
use App\Models\User;
use App\Repositories\Shipping\ShipmentRepo;

class ListMyQueueAction
{
    public function __construct(
        private readonly ShipmentRepo $shipments,
        private readonly ShipmentListPayloadHelper $helper,
    ) {}

    public function execute(User $user, int $perPage = 25): array
    {
        $page = $this->shipments->paginateAssignedTo(
            userId: (int) $user->id,
            statuses: ['purchased', 'packed'],
            perPage: $perPage,
        );

        return $this->helper->toListPayload($page);
    }
}

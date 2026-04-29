<?php

namespace App\Actions\Shipments;

use App\Helpers\Shipments\ShipmentListPayloadHelper;
use App\Repositories\Shipping\ShipmentRepo;

class ListShipmentsAction
{
    public function __construct(
        private readonly ShipmentRepo $shipments,
        private readonly ShipmentListPayloadHelper $helper,
    ) {}

    public function execute(?string $status = null, ?string $carrier = null, ?string $q = null, int $perPage = 25): array
    {
        $page = $this->shipments->paginateScoped(
            filter: ['status' => $status, 'carrier' => $carrier, 'q' => $q],
            with: ['toAddress', 'assignee', 'requester'],
            perPage: $perPage,
        );

        return $this->helper->toListPayload($page, includePerPage: true);
    }
}

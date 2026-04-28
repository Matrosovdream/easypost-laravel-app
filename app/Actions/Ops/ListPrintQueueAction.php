<?php

namespace App\Actions\Ops;

use App\Helpers\Ops\PrintQueueHelper;
use App\Models\User;
use App\Repositories\Shipping\ShipmentRepo;

class ListPrintQueueAction
{
    public function __construct(
        private readonly ShipmentRepo $shipments,
        private readonly PrintQueueHelper $helper,
    ) {}

    public function execute(User $user): array
    {
        abort_unless(in_array('labels.print', $user->rights(), true), 403);

        return $this->helper->toListPayload($this->shipments->printQueue());
    }
}

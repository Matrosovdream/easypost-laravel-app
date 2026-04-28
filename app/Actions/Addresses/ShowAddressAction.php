<?php

namespace App\Actions\Addresses;

use App\Helpers\Addresses\AddressHelper;
use App\Repositories\Address\AddressRepo;
use Illuminate\Support\Facades\Gate;

class ShowAddressAction
{
    public function __construct(
        private readonly AddressRepo $addresses,
        private readonly AddressHelper $helper,
    ) {}

    public function execute(int $id): array
    {
        $address = $this->addresses->getModel()->newQuery()->find($id);
        abort_if(! $address, 404);
        Gate::authorize('view', $address);

        return $this->helper->toDetail($address);
    }
}

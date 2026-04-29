<?php

namespace App\Actions\Addresses;

use App\Repositories\Address\AddressRepo;
use Illuminate\Support\Facades\Gate;

class DeleteAddressAction
{
    public function __construct(
        private readonly AddressRepo $addresses,
    ) {}

    public function execute(int $id): array
    {
        $address = $this->addresses->getModel()->newQuery()->find($id);
        abort_if(! $address, 404);
        Gate::authorize('delete', $address);

        $this->addresses->deleteRow($address);

        return ['ok' => true];
    }
}

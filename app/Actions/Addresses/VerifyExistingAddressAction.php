<?php

namespace App\Actions\Addresses;

use App\Models\Address;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Address\AddressRepo;

class VerifyExistingAddressAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly AddressRepo $addresses,
    ) {}

    public function execute(Address $address): Address
    {
        if (! $address->ep_address_id) {
            return $address;
        }

        try {
            $resp = $this->ep->verifyAddress($address->ep_address_id);
            $success = (bool) ($resp['verifications']['delivery']['success'] ?? false);
            $this->addresses->markVerified($address, $success, $resp['verifications'] ?? null);
        } catch (\Throwable) {
            // no-op; keep previous state
        }

        return $address->fresh();
    }
}

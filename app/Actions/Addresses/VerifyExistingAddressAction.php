<?php

namespace App\Actions\Addresses;

use App\Helpers\Addresses\AddressHelper;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Address\AddressRepo;
use Illuminate\Support\Facades\Gate;

class VerifyExistingAddressAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly AddressRepo $addresses,
        private readonly AddressHelper $helper,
    ) {}

    public function execute(int $id): array
    {
        $address = $this->addresses->getModel()->newQuery()->find($id);
        abort_if(! $address, 404);
        Gate::authorize('verify', $address);

        if (! $address->ep_address_id) {
            return $this->helper->toDetail($address);
        }

        try {
            $resp = $this->ep->verifyAddress($address->ep_address_id)->json();
            $success = (bool) ($resp['verifications']['delivery']['success'] ?? false);
            $address = $this->addresses->markVerified($address, $success, $resp['verifications'] ?? null);
        } catch (\Throwable) {
            // no-op; keep previous state
            $address = $address->fresh();
        }

        return $this->helper->toDetail($address);
    }
}

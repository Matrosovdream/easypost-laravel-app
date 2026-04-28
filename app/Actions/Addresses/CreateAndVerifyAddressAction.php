<?php

namespace App\Actions\Addresses;

use App\Helpers\Addresses\AddressHelper;
use App\Models\Address;
use App\Models\User;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Address\AddressRepo;

class CreateAndVerifyAddressAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly AddressRepo $addresses,
        private readonly AddressHelper $helper,
    ) {}

    public function execute(User $user, array $input, bool $verify = true): array
    {
        $teamId = (int) $user->current_team_id;

        $ep = null;
        if ($verify) {
            try {
                $ep = $this->ep->createAndVerifyAddress($this->toEp($input));
            } catch (\Throwable) {
                // Fall through and create locally unverified
            }
        }

        $address = $this->addresses->createForTeam($teamId, [
            'client_id' => $input['client_id'] ?? null,
            'ep_address_id' => $ep['id'] ?? null,
            'name' => $ep['name'] ?? ($input['name'] ?? null),
            'company' => $ep['company'] ?? ($input['company'] ?? null),
            'street1' => $ep['street1'] ?? $input['street1'],
            'street2' => $ep['street2'] ?? ($input['street2'] ?? null),
            'city' => $ep['city'] ?? ($input['city'] ?? null),
            'state' => $ep['state'] ?? ($input['state'] ?? null),
            'zip' => $ep['zip'] ?? ($input['zip'] ?? null),
            'country' => $ep['country'] ?? $input['country'],
            'phone' => $ep['phone'] ?? ($input['phone'] ?? null),
            'email' => $ep['email'] ?? ($input['email'] ?? null),
            'residential' => $ep['residential'] ?? ($input['residential'] ?? null),
            'verified' => (bool) ($ep['verifications']['delivery']['success'] ?? false),
            'verified_at' => ($ep['verifications']['delivery']['success'] ?? false) ? now() : null,
            'verification' => $ep['verifications'] ?? null,
        ]);

        return $this->helper->toDetail($address);
    }

    private function toEp(array $i): array
    {
        return array_filter([
            'name' => $i['name'] ?? null,
            'company' => $i['company'] ?? null,
            'street1' => $i['street1'],
            'street2' => $i['street2'] ?? null,
            'city' => $i['city'] ?? null,
            'state' => $i['state'] ?? null,
            'zip' => $i['zip'] ?? null,
            'country' => $i['country'],
            'phone' => $i['phone'] ?? null,
            'email' => $i['email'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
    }
}

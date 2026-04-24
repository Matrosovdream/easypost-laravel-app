<?php

namespace App\Actions\Pickups;

use App\Models\Pickup;
use App\Models\User;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Address\AddressRepo;
use App\Repositories\Operations\PickupRepo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SchedulePickupAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly PickupRepo $pickups,
        private readonly AddressRepo $addresses,
    ) {}

    public function execute(User $user, array $input): Pickup
    {
        $teamId = (int) $user->current_team_id;

        $address = $this->addresses->findInTeam($teamId, (int) $input['address_id']);
        if (! $address) {
            throw new RuntimeException('Address not found for this team.');
        }

        return DB::transaction(function () use ($user, $teamId, $address, $input) {
            $payload = [
                'address' => ['id' => $address->ep_address_id],
                'min_datetime' => $input['min_datetime'],
                'max_datetime' => $input['max_datetime'],
                'instructions' => $input['instructions'] ?? null,
                'is_account_address' => (bool) ($input['is_account_address'] ?? false),
                'reference' => $input['reference'] ?? null,
            ];

            $epResp = null;
            try {
                if ($address->ep_address_id) {
                    $epResp = $this->ep->createPickup(array_filter($payload, fn ($v) => $v !== null));
                }
            } catch (\Throwable) {
                // keep local pickup in unknown state; user can retry buy
            }

            return $this->pickups->create([
                'team_id' => $teamId,
                'ep_pickup_id' => $epResp['id'] ?? null,
                'reference' => $input['reference'] ?? null,
                'warehouse_id' => $input['warehouse_id'] ?? null,
                'address_id' => $address->id,
                'min_datetime' => $input['min_datetime'],
                'max_datetime' => $input['max_datetime'],
                'instructions' => $input['instructions'] ?? null,
                'is_account_address' => (bool) ($input['is_account_address'] ?? false),
                'rates_snapshot' => $epResp['pickup_rates'] ?? null,
                'status' => $epResp ? 'unknown' : 'creating',
                'created_by' => $user->id,
            ])['Model'];
        });
    }
}

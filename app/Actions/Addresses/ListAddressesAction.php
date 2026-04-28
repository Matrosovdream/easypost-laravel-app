<?php

namespace App\Actions\Addresses;

use App\Helpers\Addresses\AddressHelper;
use App\Models\Address;
use App\Models\User;
use App\Repositories\Address\AddressRepo;
use Illuminate\Support\Facades\Gate;

class ListAddressesAction
{
    public function __construct(
        private readonly AddressRepo $addresses,
        private readonly AddressHelper $helper,
    ) {}

    public function execute(User $user, ?string $search = null, int $perPage = 25): array
    {
        Gate::authorize('viewAny', Address::class);

        $page = $this->addresses->paginateForTeam(
            teamId: (int) $user->current_team_id,
            search: $search,
            perPage: $perPage,
        );

        return $this->helper->toListPayload($page);
    }
}

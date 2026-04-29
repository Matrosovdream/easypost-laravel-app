<?php

namespace App\Actions\Clients;

use App\Helpers\Clients\ClientHelper;
use App\Models\Client;
use App\Models\User;
use App\Repositories\Client\ClientRepo;
use Illuminate\Support\Facades\Gate;

class ListClientsAction
{
    public function __construct(
        private readonly ClientRepo $clients,
        private readonly ClientHelper $helper,
    ) {}

    public function execute(User $user): array
    {
        Gate::authorize('viewAny', Client::class);

        return $this->helper->toListPayload(
            $this->clients->forTeam((int) $user->current_team_id)
        );
    }
}

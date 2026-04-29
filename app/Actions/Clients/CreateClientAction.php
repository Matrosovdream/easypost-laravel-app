<?php

namespace App\Actions\Clients;

use App\Helpers\Clients\ClientHelper;
use App\Models\Client;
use App\Models\User;
use App\Repositories\Client\ClientRepo;

class CreateClientAction
{
    public function __construct(
        private readonly ClientRepo $clients,
        private readonly ClientHelper $helper,
    ) {}

    public function execute(User $user, array $input): array
    {
        /** @var Client $client */
        $client = $this->clients->create(array_merge($input, [
            'team_id' => $user->current_team_id,
            'status' => $input['status'] ?? 'active',
        ]))['Model'];

        return $this->helper->toDetail($client);
    }
}

<?php

namespace App\Actions\Clients;

use App\Helpers\Clients\ClientHelper;
use App\Repositories\Client\ClientRepo;
use Illuminate\Support\Facades\Gate;

class UpdateClientAction
{
    public function __construct(
        private readonly ClientRepo $clients,
        private readonly ClientHelper $helper,
    ) {}

    public function execute(int $id, array $input): array
    {
        $client = $this->clients->getModel()->newQuery()->find($id);
        abort_if(! $client, 404);
        Gate::authorize('update', $client);

        $client = $this->clients->updateAttributes($client->id, $input);

        return $this->helper->toDetail($client);
    }
}

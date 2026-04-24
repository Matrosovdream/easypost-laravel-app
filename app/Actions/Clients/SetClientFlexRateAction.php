<?php

namespace App\Actions\Clients;

use App\Models\Client;
use App\Repositories\Client\ClientRepo;

class SetClientFlexRateAction
{
    public function __construct(private readonly ClientRepo $clients) {}

    public function execute(Client $client, float $markupPct, ?array $perService = null): Client
    {
        return $this->clients->setFlexRate($client, $markupPct, $perService);
    }
}

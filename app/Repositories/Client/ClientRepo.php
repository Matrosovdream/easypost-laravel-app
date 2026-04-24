<?php

namespace App\Repositories\Client;

use App\Models\Client;
use App\Repositories\AbstractRepo;
use Illuminate\Database\Eloquent\Collection;

class ClientRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new Client();
    }

    public function forTeam(int $teamId): Collection
    {
        return Client::where('team_id', $teamId)->orderBy('company_name')->get();
    }

    public function findInTeam(int $teamId, int $id): ?Client
    {
        return Client::where('team_id', $teamId)->find($id);
    }

    public function updateAttributes(int $id, array $data): ?Client
    {
        $client = Client::find($id);
        if (! $client) return null;
        $client->fill($data)->save();
        return $client->fresh();
    }

    public function setFlexRate(Client $client, float $markupPct, ?array $perService): Client
    {
        $client->forceFill([
            'flexrate_markup_pct' => $markupPct,
            'per_service_markups' => $perService,
        ])->save();
        return $client->fresh();
    }
}

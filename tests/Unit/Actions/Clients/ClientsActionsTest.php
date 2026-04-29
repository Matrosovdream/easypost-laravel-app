<?php

use App\Actions\Clients\CreateClientAction;
use App\Actions\Clients\ListClientsAction;
use App\Helpers\Clients\ClientHelper;
use App\Models\Client;
use App\Models\User;
use App\Repositories\Client\ClientRepo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->repo = mock(ClientRepo::class);
    $this->helper = new ClientHelper();
});

it('ListClientsAction authorizes then returns shaped collection', function () {
    Gate::shouldReceive('authorize')->with('viewAny', Client::class)->once();
    $this->repo->shouldReceive('forTeam')->with(3)->andReturn(new Collection());

    $action = new ListClientsAction($this->repo, $this->helper);
    $user = new User();
    $user->current_team_id = 3;

    $out = $action->execute($user);
    expect($out['data']->count())->toBe(0);
});

it('CreateClientAction creates with team_id+default status and returns detail', function () {
    $action = new CreateClientAction($this->repo, $this->helper);

    $client = new Client(['company_name' => 'Acme', 'flexrate_markup_pct' => 0]);
    $client->id = 1;

    $this->repo->shouldReceive('create')
        ->withArgs(fn ($input) => $input['team_id'] === 3 && $input['status'] === 'active')
        ->andReturn(['Model' => $client]);

    $user = new User();
    $user->current_team_id = 3;

    expect($action->execute($user, ['company_name' => 'Acme'])['company_name'])->toBe('Acme');
});
